# Centralised Zoom Token Generator
Making a centralised way to manage zoom api tokens for server to server API apps

### Key Issue = When using multiple deployments the zoom token will get overwritten and cause issues

Related Issues: https://devforum.zoom.us/t/server-to-server-oauth/82901, https://devforum.zoom.us/t/issue-migrating-jwt-to-server-to-server-oauth/82340

With the Zoom's replacement to JWT token, an issue arised when using token in multi-deployed applications (e.g. whitelabled apps).

![SB Tech-Zoom API drawio (2)](https://user-images.githubusercontent.com/5123109/228689626-2016a7f1-fd32-4bfd-8302-6f9e083041c2.png)

## 🔐 Further Security
- Server Token Based
- Allow-list only the server IPs when deployed


#### Sample Request

```curl
curl --location 'hosted-url' \
--header 'ACCESS_KEY: YOUR_OWN_PRIVATE_KEY'
```

*Open source software disclaimer: Use at your own risk.*
