# Linear Webhook Proxy

A simple Node.js proxy server that receives incoming Linear webhook requests and forwards them to your local WordPress installation running the Linear P2 plugin.

## Purpose

This proxy allows you to use [ngrok](https://ngrok.com) to expose your local WordPress webhook endpoint to the internet. Once you have ngrok installed, you can run the proxy server and use the provided URL to configure your Linear webhook.

Run ngrok with this command:

```
ngrok http http://localhost:3000
```

The proxy assumes a non-permaline URL structure, which is the default for wp-env installations. Simply append `webhook` to the ngrok URL and enter that URL into the Linear webhook configuration.

## Setup

1. Install dependencies:
   ```
   cd webhook-proxy
   npm install
   ```

2. Start the server:
   ```
   npm start
   ```
