// src/services/SyncService.ts
import { db, type SyncJob } from '../db';
import client from '../api/client';

class SyncService {
    isSyncing = false;

    constructor() {
        window.addEventListener('online', () => {
            console.log('Online! Attempting sync...');
            this.sync();
        });
    }

    async sync() {
        if (this.isSyncing || !navigator.onLine) return;

        this.isSyncing = true;
        window.dispatchEvent(new Event('sync-start'));

        try {
            const jobs = await db.syncQueue
                .where('status')
                .equals('pending')
                .toArray();

            if (jobs.length === 0) {
                this.isSyncing = false;
                window.dispatchEvent(new Event('sync-complete'));
                return;
            }

            console.log(`Syncing ${jobs.length} jobs...`);

            for (const job of jobs) {
                try {
                    await this.processJob(job);
                    await db.syncQueue.delete(job.id!);
                } catch (e) {
                    console.error(`Job ${job.id} failed`, e);
                    // Optionally mark as failed or retry count?
                    // For now, leave it effectively 'pending' or move to failed to avoid loop
                }
            }

            // Trigger a reload or event so UI updates?
            window.dispatchEvent(new Event('sync-complete'));

        } catch (error) {
            console.error('Sync failed', error);
            window.dispatchEvent(new Event('sync-complete')); // End syncing state even on error
        } finally {
            this.isSyncing = false;
        }
    }

    async processJob(job: SyncJob) {
        switch (job.type) {
            case 'create_invoice':
                await client.post('/invoices', job.payload);
                break;
            case 'create_customer':
                await client.post('/parties', job.payload);
                break;
            // Add other cases
        }
    }

    async addToQueue(type: SyncJob['type'], payload: any) {
        await db.syncQueue.add({
            type,
            payload,
            createdAt: Date.now(),
            status: 'pending'
        });

        // Try to sync immediately if online
        if (navigator.onLine) {
            this.sync();
        }
    }
}

export const syncService = new SyncService();
