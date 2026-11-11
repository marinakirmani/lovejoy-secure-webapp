# Security notes

Lovejoy is an educational prototype, not a production-ready application. Run locally with fictional data. The public snapshot omits personal records, test passwords and local debugging endpoints.

Before any external deployment, review and implement: secure credential management, TLS-only hosting and secure session cookies, email-based reset-token delivery, rate limiting for password reset and login, security testing and improved upload isolation, logging/monitoring, and a full threat model. Security controls present in source code have not been independently penetration-tested.
