#!/bin/bash
# Fix line 23 - the broken address line
sed -i '' '23s/.*/                                <p class="text-\[10px\] mt-0.5">{{ invoice.business?.meta?.address_line_1 || '\''Kewalpurwa Nagar'\'' }}<\/p>/' src/views/invoices/layouts/ClassicGridLayout.vue
