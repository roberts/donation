# Google Maps API Setup for Address Autocomplete

To enable the address autocomplete feature on the donation form, you need a valid Google Maps API key.

## Prerequisites
- A Google Cloud Platform (GCP) account.
- Billing enabled on the project (Google provides a $200 monthly free credit, which covers approximately 28,000 loads).

## Setup Instructions

1. **Create a Project**
   - Go to the [Google Cloud Console](https://console.cloud.google.com/).
   - Create a new project or select an existing one.

2. **Enable APIs**
   - Navigate to **APIs & Services > Library**.
   - Search for and enable the following APIs:
     - **Places API** (New) or Places API
     - **Maps JavaScript API**

3. **Create Credentials**
   - Navigate to **APIs & Services > Credentials**.
   - Click **Create Credentials** > **API Key**.
   - Copy the generated API key.

4. **Restrict Your Key (Critical for Security)**
   - Click on the newly created API key to edit settings.
   - **Application Restrictions:** Select **Websites (HTTP referrers)**.
   - Add your domains (e.g., `https://app.ibefoundation.org/*`, `https://ibefoundation.org/*`).
   - **API Restrictions:** Select **Restrict key** and check only:
     - Places API
     - Maps JavaScript API
   - Save changes.

5. **Configure Application**
   - Open your `.env` file.
   - Add the key:
     ```env
     GOOGLE_MAPS_API_KEY=your_api_key_here
     ```
