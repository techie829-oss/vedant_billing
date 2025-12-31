
/**
 * Smart Parser for Quick Note / Digital Receipt
 * 
 * Goals:
 * 1. Extract Quantity, Product Name, and Price from a single string.
 * 2. Handle varied formats like:
 *    - "2 Fan 1500" -> Qty: 2, Name: Fan, Price: 1500
 *    - "Wire 10m 50" -> Qty: 10, Unit: m, Name: Wire, Price: 50 (if possible, simplified to name for now)
 *    - "Switch 10"   -> Qty: 1, Name: Switch, Price: 10 (Ambiguous: 10 qty or 10 price?)
 *       -> Rule: If last number is large, it's price. If small? Valid question.
 *       -> Default Rule: Last number is usually price. First number is usually Quantity.
 *    - "500 labour"  -> Qty: 1, Name: Labour, Price: 500
 */

export interface ParsedItem {
    raw: string;
    qty: number;
    name: string;
    price: number;
    total: number;
    is_valid: boolean;
    is_percentage?: boolean;
    percentage_val?: number;
}

export function parseLine(input: string): ParsedItem {
    const trimmed = input.trim();
    if (!trimmed) {
        return { raw: input, qty: 1, name: '', price: 0, total: 0, is_valid: false };
    }

    // Pattern Percentage: "(-) 10% Discount" or "total - 10%"
    // Regex: Optional +/- , then Number, then %, then Text
    // Also handle "total - 15%" specifically as a variation

    // Clean up "total" keyword if present at start to simplify
    let cleanInput = trimmed;
    if (cleanInput.toLowerCase().startsWith('total')) {
        cleanInput = cleanInput.replace(/^total\s*/i, '');
    }

    const percentRegex = /^([+-]?)\s*(\d+(\.\d+)?)\s*%\s*(.*)$/;
    const percentMatch = cleanInput.match(percentRegex);
    if (percentMatch) {
        // Default to mobile if just "10%"? Usually discount is minus. But let's check sign.
        // If no sign, check context? No, usually "10% Tax" is + and "10% Disc" is -. 
        // For now, if no sign, assume POSITIVE (Tax) unless text contains 'off'/'disc'.
        let signStr = percentMatch[1] || '';
        const numberVal = parseFloat(percentMatch[2] || '0');
        const text = (percentMatch[4] || '').trim();

        let sign = 1;
        if (signStr === '-') {
            sign = -1;
        } else if (signStr === '') {
            // Infer from text
            if (/discount|off|less|deduct/i.test(text)) {
                sign = -1;
            }
        }

        return {
            raw: input,
            qty: 1,
            name: text || (sign === -1 ? 'Discount' : 'Tax/Charge'),
            price: 0, // Will be calculated by consumer
            total: 0, // Will be calculated by consumer
            is_valid: true,
            is_percentage: true,
            percentage_val: numberVal * sign
        };
    }

    // Pattern Percentage Variation: "Text Number%" (e.g. "Discount 10%")
    const textPercentRegex = /^(.*?)\s*(\d+(\.\d+)?)\s*%$/;
    const textPercentMatch = cleanInput.match(textPercentRegex);
    if (textPercentMatch) {
        const text = (textPercentMatch[1] || '').trim();
        const numberVal = parseFloat(textPercentMatch[2] || '0');

        // In this format, heuristic: "Discount 10%" -> usually minus. "Tax 10%" -> usually plus.
        // If generic, assume minus if "Discount" key present, else plus? 
        // Actually, safer to treat as "Percentage Value" unsigned, and let caller decide or use keywords.
        // But "Discount 10%" implies -10%. 

        let sign = 1;
        if (/discount|off|less|deduct/i.test(text)) {
            sign = -1;
        }

        return {
            raw: input,
            qty: 1,
            name: text,
            price: 0,
            total: 0,
            is_valid: true,
            is_percentage: true,
            percentage_val: numberVal * sign
        };
    }

    // Pattern 0a: Signed Text + Amount (e.g. "- Fan 500" or "+ Charger 200")
    // This allows the user to write the item name first but still have it mathematically processed
    const signedTextRegex = /^([+-])\s*(.+?)\s+(\d+(\.\d+)?)$/;
    const signedTextMatch = trimmed.match(signedTextRegex);
    if (signedTextMatch) {
        const sign = signedTextMatch[1] === '-' ? -1 : 1;
        const text = (signedTextMatch[2] || '').trim();
        const amount = parseFloat(signedTextMatch[3] || '0');

        return {
            raw: input,
            qty: 1,
            name: text,
            price: amount,
            total: amount * sign,
            is_valid: true
        };
    }

    // Pattern 0: Signed Amount (e.g., "- 1000 Jama" or "+ 500 Interest")
    // Regex: Starts with + or -, then Number, then optional Text
    const signedRegex = /^([+-])\s*(\d+(\.\d+)?)\s*(.*)$/;
    const signedMatch = trimmed.match(signedRegex);
    if (signedMatch) {
        const sign = signedMatch[1] === '-' ? -1 : 1;
        const amount = parseFloat(signedMatch[2]);
        const text = (signedMatch[4] || '').trim();

        return {
            raw: input,
            qty: 1,
            price: amount,
            name: text || (sign === -1 ? 'Deduction' : 'Addition'),
            total: amount * sign,
            is_valid: true
        };
    }

    // Pattern 1: Explicit Math "5 * 500" or "500 / 5"
    // Checks for a format like "NUMBER operator NUMBER" with optional text
    const mathRegex = /^(\d+(\.\d+)?)\s*([*x\/])\s*(\d+(\.\d+)?)\s*(.*)$/i;
    const mathMatch = trimmed.match(mathRegex);
    if (mathMatch) {
        const num1 = parseFloat(mathMatch[1] || '0');
        const operator = mathMatch[3];
        const num2 = parseFloat(mathMatch[4] || '0');
        const restText = (mathMatch[6] || '').trim();

        let total = 0;
        if (operator === '/' && num2 !== 0) {
            total = num1 / num2;
        } else {
            total = num1 * num2;
        }

        return {
            raw: input,
            qty: num1,
            price: num2,
            name: restText || (operator === '/' ? 'Division' : 'Multiplication'),
            total: total,
            is_valid: true
        };
    }

    // Pattern 2: "Qty Item Price" (e.g., "2 Fan 1200")
    // Regex: Start with Number, then Text, end with Number
    const qtyFirstRegex = /^(\d+(\.\d+)?)\s+(.+?)\s+(\d+(\.\d+)?)$/;
    const qtyFirstMatch = trimmed.match(qtyFirstRegex);

    if (qtyFirstMatch) {
        return {
            raw: input,
            qty: parseFloat(qtyFirstMatch[1] || '0'),
            name: (qtyFirstMatch[3] || '').trim(),
            price: parseFloat(qtyFirstMatch[4] || '0'),
            total: parseFloat(qtyFirstMatch[1] || '0') * parseFloat(qtyFirstMatch[4] || '0'),
            is_valid: true
        };
    }

    // Pattern 3: "Item Price" (e.g., "Labor 500") -> Qty 1
    // Ends with a number
    const endsWithNumberRegex = /^(.*?)\s+(\d+(\.\d+)?)$/;
    const endsWithNumberMatch = trimmed.match(endsWithNumberRegex);

    if (endsWithNumberMatch) {
        const textPart = (endsWithNumberMatch[1] || '').trim();
        // Check if text part starts with a number that wasn't caught by Pattern 2
        // e.g. "2 Fan" (Price missing) vs "Fan 500" (Qty missing)

        // If the text part is just a name
        return {
            raw: input,
            qty: 1,
            name: textPart || 'Item',
            price: parseFloat(endsWithNumberMatch[2] || '0'),
            total: 1 * parseFloat(endsWithNumberMatch[2] || '0'),
            is_valid: true
        };
    }

    // Pattern 4: "Qty Item" (e.g., "2 Fan") -> Price 0
    // Starts with number
    const startsWithNumberRegex = /^(\d+(\.\d+)?)\s+(.*)$/;
    const startsWithNumberMatch = trimmed.match(startsWithNumberRegex);

    if (startsWithNumberMatch) {
        return {
            raw: input,
            qty: parseFloat(startsWithNumberMatch[1] || '0'),
            name: (startsWithNumberMatch[3] || '').trim(),
            price: 0,
            total: 0,
            is_valid: true
        };
    }

    // Fallback: Just text
    return {
        raw: input,
        qty: 1,
        name: trimmed,
        price: 0,
        total: 0,
        is_valid: true // Still valid, just needs info filled
    };
}
