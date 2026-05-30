# Issue Tracker: GitHub

Issues and PRDs for this repo live as GitHub Issues on `outboardphp/outboard`.
Use the **GitHub MCP server** (`github-mcp-server`) for all operations — prefer MCP tools over the `gh` CLI.

## Operations

### Create an issue
Use `issue_write` with `method: "create"`:
```json
{ "method": "create", "owner": "outboardphp", "repo": "outboard", "title": "...", "body": "...", "labels": ["..."] }
```

### Read an issue
Use `issue_read` with `method: "get"` for details, `"get_comments"` for comments, `"get_labels"` for labels:
```json
{ "method": "get", "owner": "outboardphp", "repo": "outboard", "issue_number": 123 }
```

### List issues
Use `list_issues` with optional `state`, `labels`, and pagination via `after` cursor:
```json
{ "owner": "outboardphp", "repo": "outboard", "state": "OPEN", "labels": ["needs-triage"] }
```

For targeted queries (keyword search, complex filters), use `search_issues` instead.

### Comment on an issue
Use `add_issue_comment`:
```json
{ "owner": "outboardphp", "repo": "outboard", "issue_number": 123, "body": "..." }
```

### Apply / remove labels, change state, update fields
Use `issue_write` with `method: "update"`. Provide only the fields to change:
```json
{ "method": "update", "owner": "outboardphp", "repo": "outboard", "issue_number": 123, "labels": ["ready-for-agent"], "state": "closed", "state_reason": "completed" }
```

## When a skill says "publish to the issue tracker"

Create a GitHub issue using `issue_write` with `method: "create"`.

## When a skill says "fetch the relevant ticket"

Use `issue_read` with `method: "get"` and follow up with `method: "get_comments"` if discussion context is needed.

## Tips

- Call `search_issues` before creating a new issue to avoid duplicates.
- Use `list_issue_types` first if creating issues in an org with custom issue types configured.
- Paginate with batches of 5–10 items using the `after` cursor from `pageInfo`.
