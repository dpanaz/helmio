# Contributing to Helmio

Helmio is an investor-first financial monitoring platform. Changes must preserve trust, explainability, and data integrity.

## Core rules

1. Financial calculations are deterministic and tested.
2. AI may explain calculated results but may not invent or alter values.
3. Every alert must reference supporting evidence.
4. Brokerage integrations are read-only unless the product and legal posture are deliberately changed.
5. Never commit credentials, tokens, brokerage data, statements, or customer information.
6. Prefer small, reviewable commits with a single purpose.

## Branches and commits

Create branches using:

- `feature/<short-name>`
- `fix/<short-name>`
- `docs/<short-name>`
- `chore/<short-name>`

Write imperative commit messages, for example:

- `Add investor profile onboarding`
- `Fix account fee aggregation`
- `Document turnover calculation`

## Local checks

Before committing Laravel work, run:

```bash
php artisan test
npm run build
```

Before committing analytics work, run from `services/analytics`:

```bash
uv run pytest
uv run ruff check .
```

## Pull-request checklist

- [ ] The change has a focused purpose.
- [ ] Tests cover meaningful behavior.
- [ ] No secrets or private financial data are included.
- [ ] User-facing financial wording avoids unsupported legal conclusions.
- [ ] New metrics document inputs, formula version, assumptions, and edge cases.
- [ ] UI changes are responsive and accessible.

## Financial language

Use:

- “Potential concern”
- “Activity warrants review”
- “Based on available data”
- “Ask your adviser to explain”

Avoid:

- “Your adviser broke the law”
- “This is definitely churning”
- “Sell this investment”

## Security reporting

Do not open public issues containing security vulnerabilities or customer data. Report them privately to the project owner.
