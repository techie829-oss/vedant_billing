// src/db.ts
import Dexie, { type Table } from 'dexie';

export interface Invoice {
    id?: string; // Optional because offline created ones might rely on local ID first? Or we use UUIDs?
    invoice_number: string;
    // We can store the full invoice object or specific fields. 
    // Storing the full object is easier for caching.
    [key: string]: any;
}

export interface Customer {
    id?: string;
    name: string;
    [key: string]: any;
}

export interface Product {
    id?: string;
    name: string;
    [key: string]: any;
}

export interface SyncJob {
    id?: number;
    type: 'create_invoice' | 'update_customer' | 'create_customer';
    payload: any;
    createdAt: number;
    status: 'pending' | 'processing' | 'failed';
}

export class BillingDatabase extends Dexie {
    invoices!: Table<Invoice>;
    customers!: Table<Customer>;
    products!: Table<Product>;
    syncQueue!: Table<SyncJob>;

    constructor() {
        super('BillingBookDB');
        this.version(2).stores({
            invoices: 'id, invoice_number, date, customer_id, status', // id is primary key (UUID)
            customers: 'id, name, email',
            products: 'id, name',
            syncQueue: '++id, type, status' // Keep auto-increment for local job queue
        });
    }
}

export const db = new BillingDatabase();
