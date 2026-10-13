# Security Policy

## Supported Versions

| Version | Supported | PHP Requirement |
|---------|----------|----------------|
| 1.x     | ✅ Yes    | PHP 7.4 - 8.4  |

## Reporting a Vulnerability

If you discover a security vulnerability, please report it responsibly.

### Private Reporting (Preferred)

For security issues that could affect production deployments or expose sensitive health data, please **do not open a public issue**. Instead:

1. Email the maintainer directly at **mnashiddiqi01@gmail.com**
2. Use the subject line: `[SECURITY] satusehat-integration`

### What to Include

Your report should contain:
- Description of the vulnerability
- Steps to reproduce the issue
- Affected version(s)
- Potential impact assessment
- Any suggested fixes (optional)

### What to Expect
- Acknowledgment of your report within **48 hours**
- A severity assessment within **7 days**
- A fix or resolution timeline based on severity

## Bug Reports (Non-Security)

For general bugs and issues, open a public [GitHub Issue](https://github.com/newustechnology/satusehat-integration/issues).

## Security Considerations

- Never commit API credentials, client secrets, or organization IDs to version control.
- Store SATUSEHAT credentials in environment variables (`.env`), not in code.
- The library handles OAuth2 tokens internally — do not expose or log raw bearer tokens.
- FHIR resources contain PHI (Protected Health Information) — ensure your application complies with applicable data protection regulations.
