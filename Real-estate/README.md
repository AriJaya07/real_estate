# DreamRue Estate — Frontend

Nuxt 4 SPA for the Real Estate Management System. Backend API lives in `../real-estate-api/`.

## Project Documentation

| Doc | What's inside |
|---|---|
| [docs/architecture.md](docs/architecture.md) | App structure, layers, data flow, design decisions |
| [docs/environment.md](docs/environment.md) | Setup, env vars, build & deploy commands |
| [docs/api-patterns.md](docs/api-patterns.md) | How this app consumes the API, error handling |
| [docs/domain.md](docs/domain.md) | UI glossary, invariants, status meta |
| [docs/prd.md](docs/prd.md) | Screens, UX rules, acceptance criteria |
| [docs/coding-standards.md](docs/coding-standards.md) | Vue/TS/Tailwind conventions, anti-patterns |
| [docs/testing-strategy.md](docs/testing-strategy.md) | Test plan (no framework yet — vue-tsc is the gate) |
| [docs/tech-debt.md](docs/tech-debt.md) | Known debt & deliberate quirks (TS 5.9 pin!) |
| [agent-rules.md](agent-rules.md) | Rules for AI agents working in this repo |
| [ai-prompts/](ai-prompts/) | Reusable AI prompt templates |

API deployment guide: [../real-estate-api/docs/deployment.md](../real-estate-api/docs/deployment.md).

Look at the [Nuxt documentation](https://nuxt.com/docs/getting-started/introduction) to learn more.

## Setup

Make sure to install dependencies:

```bash
# npm
npm install

# pnpm
pnpm install

# yarn
yarn install

# bun
bun install
```

## Development Server

Start the development server on `http://localhost:3000`:

```bash
# npm
npm run dev

# pnpm
pnpm dev

# yarn
yarn dev

# bun
bun run dev
```

## Production

Build the application for production:

```bash
# npm
npm run build

# pnpm
pnpm build

# yarn
yarn build

# bun
bun run build
```

Locally preview production build:

```bash
# npm
npm run preview

# pnpm
pnpm preview

# yarn
yarn preview

# bun
bun run preview
```

Check out the [deployment documentation](https://nuxt.com/docs/getting-started/deployment) for more information.
