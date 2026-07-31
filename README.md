cat > README.md <<'EOF'
# Helmio

Independent investment oversight.

Helmio connects to investment accounts using read-only access and monitors:

- investment expenses
- mutual fund and ETF expense ratios
- advisory and brokerage fees
- portfolio performance
- benchmark performance
- trading activity
- turnover and possible churning indicators
- concentration and portfolio overlap
- cash movement
- risk and compliance indicators

## Repository structure

```text
apps/
  web/              Next.js customer application
  api/              Laravel application API

services/
  analytics/        Python financial analytics service

docs/
  prd/              Product requirements
  engineering-manual/

infrastructure/
  docker/

.github/
  workflows/
  