import type { CapacitorConfig } from '@capacitor/cli';

const config: CapacitorConfig = {
  appId: 'com.vedantbilling.app',
  appName: 'Vedant Billing',
  webDir: 'dist',
  server: {
    url: 'https://vedantbilling.com',
    cleartext: true
  }
};

export default config;
