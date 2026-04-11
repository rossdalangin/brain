# Headless React Starter (Next.js Example)

The AI Multi-Mind SaaS Engine is designed to be headless-ready. Below is a code example of how to consume the `amm/v1/generate` endpoint using a React frontend.

## 1. API Client Utility
```javascript
const igniteMind = async (mindId, userInput) => {
  const res = await fetch('https://your-wp-site.com/wp-json/amm/v1/generate', {
    method: 'POST',
    headers: {
      'Content-Type': 'application/json',
      'X-WP-Nonce': wp_nonce_from_server // Securely passed from WP
    },
    body: JSON.stringify({
      mind_id: mindId,
      output_type: 'business_plan',
      user_input: userInput,
      language: 'English'
    })
  });
  return res.json();
};
```

## 2. Dashboard Component
```jsx
import React, { useState } from 'react';

const MindDashboard = () => {
  const [output, setOutput] = useState('');
  const [loading, setLoading] = useState(false);

  const handleIgnite = async () => {
    setLoading(true);
    const data = await igniteMind('ceo', 'Scale my SaaS to 10k MRR');
    if (data.success) {
      setOutput(data.content);
    }
    setLoading(false);
  };

  return (
    <div className="dashboard">
      <h1>AI Multi-Mind Headless</h1>
      <button onClick={handleIgnite} disabled={loading}>
        {loading ? 'Thinking...' : 'Ignite CEO Mind'}
      </button>
      <div className="output-box">{output}</div>
    </div>
  );
};

export default MindDashboard;
```

## 3. Best Practices
- **Security**: Use WordPress Application Passwords or JWT for non-browser based headless authentication.
- **State**: Use React Query or SWR for caching the `/minds` and `/outputs` endpoints.
- **Styling**: Leverage Tailwind CSS to match the premium "Billion-Dollar" aesthetic.
