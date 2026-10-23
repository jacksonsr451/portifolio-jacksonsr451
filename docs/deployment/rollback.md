# Rollback

The VPS adapter retains each release under `/srv/portfolio/releases/<full-commit-sha>` and keeps `/srv/portfolio/current` as the active release marker. On a failed health or smoke check it starts the preceding release with the same external Compose volumes, verifies `/up`, `/pt-BR/`, and `/en/`, and restores the marker. A concurrent deploy cannot enter because `/srv/portfolio/run/deploy.lock` is held with `flock`.

Never automatically reverse schema migrations. The adapter creates a consistent SQLite backup under the persistent storage volume before migration, but an operator must assess compatibility and data-loss risk, use a forward fix where safer, and restore only from a tested backup under the recovery plan. Rollback restores application images and configuration, not destructive database history.

If the first deployment fails, the adapter stops the new project and removes the `current` marker. Investigate the remote release directory and logs before retrying.
