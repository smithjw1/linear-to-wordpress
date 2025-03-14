/**
 * Linear Webhook Proxy Server
 * 
 * This simple proxy server receives Linear webhook requests and forwards them
 * to a local WordPress installation running the Linear P2 plugin.
 */

const express = require('express');
const bodyParser = require('body-parser');
const axios = require('axios');
const morgan = require('morgan');

// Configuration
const PORT = process.env.PORT || 3000;
const TARGET_URL = 'http://localhost:8888/index.php?rest_route=/linear-wp/v1/webhook';

// Initialize Express app
const app = express();

// Middleware
app.use(morgan('dev')); // Logging
app.use(bodyParser.json()); // Parse JSON request bodies

// Root endpoint - just for testing
app.get('/', (req, res) => {
  res.send('Linear Webhook Proxy is running. Send POST requests to /webhook');
});

// Webhook endpoint
app.post('/webhook', async (req, res) => {
  console.log('Received webhook request');

  // Log the full incoming JSON payload for debugging
  console.log('Incoming webhook payload:', JSON.stringify(req.body, null, 2));

  try {
    // Get Linear signature header if present
    const linearSignature = req.header('linear-signature');

    // Forward the request to WordPress
    const response = await axios.post(TARGET_URL, req.body, {
      headers: {
        'Content-Type': 'application/json',
        // Forward the Linear signature header if present
        ...(linearSignature ? { 'linear-signature': linearSignature } : {})
      }
    });

    console.log('Successfully forwarded request to WordPress');
    console.log('WordPress response:', response.status, response.data);

    // Return the WordPress response
    return res.status(response.status).json(response.data);
  } catch (error) {
    console.error('Error forwarding webhook:', error.message);

    // If we got a response from WordPress with an error, forward it
    if (error.response) {
      console.error('WordPress error response:', error.response.status, error.response.data);
      return res.status(error.response.status).json(error.response.data);
    }

    // Otherwise, return a generic error
    return res.status(500).json({
      success: false,
      message: 'Error forwarding webhook: ' + error.message
    });
  }
});

// Start the server
app.listen(PORT, () => {
  console.log(`Linear Webhook Proxy running on port ${PORT}`);
  console.log(`Forwarding requests to: ${TARGET_URL}`);
});
