---
slug: api-error-contracts
title: API errors are part of the interface
description: Consistent, actionable error responses help clients recover without exposing internal details.
date: 2026-09-20
status: published
---

## Errors are a public contract

An API consumer needs to know whether a request can be corrected, retried, or escalated. A status code is a useful starting point, but the response shape and stability of its fields matter just as much.

Use a consistent envelope with a machine-readable code, a human-readable summary, and (when appropriate) field-level validation details. Keep those codes stable even when internal exception classes or implementation details change.

## Separate client and server concerns

Validation errors should identify correctable input without echoing sensitive values. Authentication and authorization failures should reveal no more than the caller is permitted to know. Unexpected failures should have a correlation identifier that support and operators can use to find protected server-side logs.

Do not return stack traces, SQL errors, filesystem paths, access tokens, or internal hostnames to clients. Log structured diagnostic context server-side, with secrets and personal data excluded or redacted.

## Test the contract

Test representative error responses at the HTTP boundary: status, content type, required fields, and absence of implementation detail. Document retry behavior separately for rate limits, transient upstream failures, and permanent validation errors. Consistency lets clients implement one clear recovery strategy instead of guessing from each endpoint.
