export const formatCurrency = (val: any) => new Intl.NumberFormat('en-IN', { style: 'currency', currency: 'INR' }).format(Number(val))
export const formatNumber = (val: any) => new Intl.NumberFormat('en-IN', { minimumFractionDigits: 2, maximumFractionDigits: 2 }).format(Number(val))

export const formatDate = (d: string) => d ? new Date(d).toLocaleDateString('en-IN') : ''

export const amountInWords = (num: number) => {
    if (!num) return 'Zero';
    const a = ['', 'one ', 'two ', 'three ', 'four ', 'five ', 'six ', 'seven ', 'eight ', 'nine ', 'ten ', 'eleven ', 'twelve ', 'thirteen ', 'fourteen ', 'fifteen ', 'sixteen ', 'seventeen ', 'eighteen ', 'nineteen '];
    const b = ['', '', 'twenty', 'thirty', 'forty', 'fifty', 'sixty', 'seventy', 'eighty', 'ninety'];
    const inWords = (nStr: any): string => {
        let n: any = nStr.toString();
        if (n.length > 9) return 'overflow';
        // @ts-ignore
        n = ('000000000' + n).substr(-9).match(/^(\d{2})(\d{2})(\d{2})(\d{1})(\d{2})$/);
        // @ts-ignore
        if (!n) return; var str = '';
        // @ts-ignore
        str += (n[1] != 0) ? (a[Number(n[1])] || b[n[1][0]] + ' ' + a[n[1][1]]) + 'crore ' : '';
        // @ts-ignore
        str += (n[2] != 0) ? (a[Number(n[2])] || b[n[2][0]] + ' ' + a[n[2][1]]) + 'lakh ' : '';
        // @ts-ignore
        str += (n[3] != 0) ? (a[Number(n[3])] || b[n[3][0]] + ' ' + a[n[3][1]]) + 'thousand ' : '';
        // @ts-ignore
        str += (n[4] != 0) ? (a[Number(n[4])] || b[n[4][0]] + ' ' + a[n[4][1]]) + 'hundred ' : '';
        // @ts-ignore
        str += (n[5] != 0) ? ((str != '') ? 'and ' : '') + (a[Number(n[5])] || b[n[5][0]] + ' ' + a[n[5][1]]) : '';
        return str;
    }
    const wholePart = Math.floor(num);
    const decimalPart = Math.round((num - wholePart) * 100);
    let result = inWords(wholePart);
    if (decimalPart > 0) result += ' Rupees and ' + inWords(decimalPart) + ' Paise';
    else result += ' Rupees';
    return result;
}
