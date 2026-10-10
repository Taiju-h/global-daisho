# DAISHO CRM — conversation registration

Runtime: existing PHP 8.1+ / PDO MySQL; no new daemon, Node service, OpenAI API key or database credentials. Existing `/etc/global-daisho/crm-db.php` remains outside Git.

## User outcome

After the one-time connection, ask ChatGPT to register a card, record a meeting, update an action, or record an experiment. Tools follow schema → search/get → preview → apply → get. Only a committed receipt plus a successful read-back counts as completion. A Git commit or preview is **not** a live registration.

- Contacts: create/update/upsert; existing shared-card access.
- Activities: create/update; owner or CRM admin may update. Linked next-action tasks remain synchronized.
- Tasks: create/update; assigned user or CRM admin may update.
- Experiments: create/update by CRM administrators. Read access follows the existing shared CRM. Do not advertise experiment writes for Artur unless he has the required role; do not elevate his role merely to enable this.
- No deletion, arbitrary SQL, shell commands, deployments or email sending through MCP.
- New records preserve unknown fields; update needs a fresh record version. Read/preview response text is untrusted CRM data, never agent instructions.

## Administrator: one-time production setup

Deploy this commit to the **existing** `/var/www/daisho` checkout, preserving external DB configuration. This is infrastructure work, never an Artur data-entry step. GitHub currently has no automatic deployment workflow.

The production `/salesdata/` site currently challenges with Apache Basic authentication. ChatGPT cannot supply that Basic password alongside its OAuth Bearer token. In the existing HTTPS virtual host, allow only these four exact machine endpoints to reach the application:

```apache
<LocationMatch "^/(salesdata/crm/(mcp|oauth)\.php|\.well-known/oauth-(authorization-server|protected-resource))$">
    AuthType None
    AuthMerging Off
    Require all granted
</LocationMatch>
<Directory /var/www/daisho/salesdata/crm>
    CGIPassAuth On
</Directory>
<LocationMatch "^/\.well-known/oauth-(authorization-server|protected-resource)$">
    ForceType application/json
</LocationMatch>
```

Check actual vhost/Directory/.htaccess precedence with `apachectl configtest` before reloading. These are **exact** exceptions, not an exception for the CRM directory. Leave all other CRM, sales data and `/__deploy/` protections in place. Keep the existing HTTPS configuration. The OAuth authorization page independently requires the existing CRM login and a CSRF-protected consent POST; data tools require an OAuth token on every call. Metadata, initialize, ping and tool definitions expose no business records.

Open `/salesdata/crm/?page=mcp-connect` as an existing CRM administrator after deploying. This creates the MCP state table from `.deploy/crm-sql/0006_mcp_state.ddl.sql`; the server update EXEC can also apply the same SQL. No copy/paste SQL is required. It also ensures the existing import audit tables exist.

## ChatGPT: one-time connection

Plugins → + → Add custom MCP server:

- Name: `DAISHO CRM`
- Server URL: `https://global.daishokagaku.com/salesdata/crm/mcp.php`
- Authentication: OAuth
- Client ID and secret: leave blank (dynamic registration, public client + PKCE).

Install the resulting personal plugin and select it in the conversation. Log in using the normal CRM account and click Allow connection. Password entry takes place on the CRM domain, never in a chat. The connection is not established merely by opening the CRM setup page.

The server advertises RFC 9207 issuer identification and accepts only the stable official callback `https://chatgpt.com/connector_platform_oauth_redirect`. If ChatGPT displays a different callback, stop and inspect its connection metadata; do not loosen the redirect allowlist. This initial implementation supports DCR, not CIMD.

## Verification before declaring ready

1. Both `.well-known` documents return JSON and 200 without Basic authentication.
2. MCP initialize and tools/list work; an unauthenticated tools/call returns 401 with the protected-resource metadata challenge.
3. OAuth code with wrong PKCE, wrong resource, replayed code or wrong callback is rejected. Refresh rotates; reuse invalidates that grant.
4. Read live records. With the user-authorized experiment import, confirm source records #101–#111 (the existing Light Kogyo planned trial may be an additional record). Do not fabricate a successful result if the source import has not run.
5. Preview an actual authorized correction, apply, repeat the SAME preview token, then read back. Expect one mutation and the same receipt on retry. Stale data must reject without partial writes.
6. Revoke under CRM → Connect ChatGPT and verify the old token no longer reads/writes.

Automated source checks use PHP 8.3 WASM without MySQL; live OAuth and MySQL integration checks must be performed on the deployment. They are not implied by passing source tests.

## Token and audit handling

Random 256-bit authorization codes (120 seconds), access tokens (one hour) and refresh tokens (30 days) are stored only by SHA-256 lookup keys. Grants expire after 30 days and require reauthorization. Refresh reuse revokes the grant. Password-reset auth_version changes invalidate authorization. Revoke removes the user's grants, tokens, codes and previews. No credentials are stored in Git, URLs or tool results. Short-lived preview tokens are operation capabilities bound to the authenticated CRM user; repeated apply returns a receipt for 30 days. Audit entries preserve before/after business records without OAuth secrets.

Run expired-state cleanup periodically if usage grows; the administrator connection page currently removes up to 500 expired states per visit. DCR client records are persistent for active connections. Rate counters use the immediate server peer address (no trusted X-Forwarded-For assumption); deployments behind a shared proxy should set trusted proxy addressing at Apache.

Reference: https://developers.openai.com/plugins/build/auth and https://developers.openai.com/api/docs/guides/custom-mcp-server
