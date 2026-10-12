---
slug: idempotent-apis
title: Designing idempotent API operations
description: A practical framework for deciding where idempotency belongs and how to make retries safe.
date: 2026-10-10
status: published
---

## Why retries need a contract

Networks fail between the moment a server commits a change and the moment a client receives the response. A client that cannot distinguish a lost response from a failed operation may retry. If the operation is not safe to repeat, that retry can create duplicate side effects.

Idempotency means that applying the same operation more than once has the same intended effect as applying it once. It does not mean every response has to be byte-for-byte identical, or that all HTTP methods are automatically safe in every implementation.

## Choose the boundary deliberately

For a create operation with meaningful retry risk, clients can send an idempotency key. Scope the key to the authenticated principal and operation, validate its format, and define a retention period. Store a request fingerprint alongside the outcome so that reusing a key with a different payload is rejected rather than silently misapplied.

The key record and business write must be coordinated transactionally where the persistence system allows it. Concurrent requests with the same key need a unique constraint or equivalent serialization point; a check-then-insert in application code alone leaves a race.

## Make outcomes observable

Record whether a key is processing or complete. Define what happens when an operation fails before commit and when a downstream side effect is involved. An outbox or a downstream idempotency contract may be needed to bridge a database transaction and an external service.

Test the duplicate request, a changed payload for the same key, concurrent submissions, and recovery after failure. Document key scope, expiration, and client retry behavior in the API contract. These details turn “retry safely” from an assumption into a behavior consumers can rely on.
