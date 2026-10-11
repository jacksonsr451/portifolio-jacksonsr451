# Pull Request

## Summary

<!-- Describe what changed, why the change is necessary, and what problem it solves. -->

### Changes
- <!-- Describe a change. -->
- <!-- Describe a change. -->
- <!-- Describe a change. -->

### Motivation

<!-- Explain the technical or functional reason for this pull request. -->

## Type of Change

- [ ] Bug fix
- [ ] New feature
- [ ] Refactoring
- [ ] Performance improvement
- [ ] Security improvement
- [ ] Documentation
- [ ] CI/CD or infrastructure
- [ ] Dependency update
- [ ] Breaking change

## Related Issues

<!-- Link related issues using Fixes #123 or Closes #123 when applicable. -->

Fixes #

## Implementation Details

<!-- Explain relevant technical decisions, architecture changes, and trade-offs. -->

### Architecture
- <!-- Describe a design/implementation decision. -->

### Dependencies
- [ ] No new dependencies
- [ ] New dependencies have been reviewed and justified
- [ ] Dependency lockfiles have been updated when necessary

### Configuration
- [ ] No configuration changes
- [ ] Environment variables are documented in `.env.example`
- [ ] No secrets or credentials are committed

## Verification

### Automated Tests

- [ ] `composer test`
- [ ] `composer lint`
- [ ] `composer analyse`
- [ ] `php artisan test --coverage --min=80`
- [ ] `npm ci`
- [ ] `npm run build`

### Security Checks

- [ ] `composer audit`
- [ ] `npm audit`
- [ ] No sensitive information exposed
- [ ] Input validation reviewed where applicable
- [ ] Authorization and access controls reviewed where applicable

### CI/CD

- [ ] GitHub Actions checks pass
- [ ] Workflow permissions follow least privilege
- [ ] No existing quality gates were bypassed
- [ ] Deployment configuration reviewed if affected

### Manual Verification

<!-- Describe manual testing steps and actual results. Use N/A when not applicable. -->

1. <!-- Verification step. -->
2. <!-- Verification step. -->
3. <!-- Verification step. -->

## Test Results

<!-- Provide actual results. Do not claim tests passed unless executed. -->

| Check | Result | Evidence |
|---|---|---|
| Unit tests | Pending | |
| Feature tests | Pending | |
| Code coverage | Pending | |
| Code formatting | Pending | |
| Static analysis | Pending | |
| Frontend build | Pending | |
| Security audit | Pending | |
| GitHub Actions | Pending | |

## Content Evidence

<!-- Required when changing professional claims, projects, articles, or open-source contributions. -->

- [ ] No portfolio content changes
- [ ] Project descriptions verified against repositories
- [ ] Technology stacks verified
- [ ] Professional experience claims verified
- [ ] Open-source contribution status verified
- [ ] External links checked
- [ ] No fabricated metrics, testimonials, or achievements

**Evidence / References:**

<!-- Include repository links, issue references, documentation, or other supporting evidence. -->

## UI/UX and Accessibility

- [ ] No UI changes
- [ ] Responsive layout verified
- [ ] Keyboard navigation verified
- [ ] Semantic HTML reviewed
- [ ] Color contrast reviewed
- [ ] Focus indicators verified
- [ ] Reduced-motion preferences respected
- [ ] No unintended layout shifts

### Screenshots

<!-- Attach before/after screenshots for visual changes. Otherwise write N/A. -->

**Before:**

**After:**

## SEO and Performance

- [ ] No SEO or performance impact
- [ ] Page titles and descriptions reviewed
- [ ] Canonical URLs reviewed
- [ ] Sitemap and robots.txt verified
- [ ] Structured data validated
- [ ] Asset optimization reviewed
- [ ] Performance regressions evaluated

## Operational Impact

### Database

- [ ] No database changes
- [ ] Migrations included and tested
- [ ] Data compatibility reviewed
- [ ] Rollback strategy documented

### Deployment

- [ ] No deployment changes
- [ ] Deployment configuration updated
- [ ] Environment requirements documented
- [ ] Health checks reviewed
- [ ] Production secrets remain protected

### Observability

- [ ] No observability changes
- [ ] Error handling reviewed
- [ ] Logging reviewed
- [ ] Sensitive information excluded from logs

## Risk Assessment

**Risk Level:**
- [ ] Low
- [ ] Medium
- [ ] High
- [ ] Critical

### Identified Risks

<!-- Describe potential regressions, compatibility issues, and operational risks. -->

### Mitigation

<!-- Explain how the identified risks are addressed. -->

## Rollback Plan

<!-- Describe how to safely revert this change, including database and deployment considerations. -->

## Documentation

- [ ] README updated if necessary
- [ ] Architecture documentation updated if necessary
- [ ] ADR added or updated if necessary
- [ ] Testing documentation updated if necessary
- [ ] Deployment documentation updated if necessary
- [ ] No documentation changes required

## Reviewer Checklist

- [ ] Scope matches the related issue or requirement
- [ ] Implementation follows project conventions
- [ ] No unnecessary complexity introduced
- [ ] Tests cover relevant positive and negative scenarios
- [ ] Error handling is appropriate
- [ ] Security implications reviewed
- [ ] Documentation matches implementation
- [ ] CI checks pass
- [ ] No unresolved blocking comments

## Final Checklist

- [ ] Changes are focused and appropriately scoped
- [ ] Code has been self-reviewed
- [ ] Temporary debugging code removed
- [ ] No secrets or local environment files committed
- [ ] All required tests pass
- [ ] Breaking changes documented
- [ ] Operational impact documented
- [ ] Pull request is ready for review

---

**Additional Notes**

<!-- Include any remaining limitations, follow-up tasks, or reviewer guidance. -->
