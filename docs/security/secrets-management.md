# Secrets management

Never commit `.env`, private keys, tokens, credentials, or real contact data without explicit approval. Use local `.env` for development. Production secrets belong in the selected hosting provider's secret store and narrowly scoped GitHub Actions environment secrets. Rotate exposed credentials, invalidate affected sessions/tokens, inspect history, and document incident response. Public repo secret scanning and push protection require repository settings and possibly an eligible GitHub plan; they are not activated by this repository content alone.
