# Contributing to TouchEstate Agency Theme

Thank you for considering contributing to this project!

## Branches

- `main` — stable production-ready code
- `develop` — active development branch

All pull requests must be submitted against the `develop` branch.

## How to Contribute

1. Fork the repository
2. Create a new branch from `develop`:
   ```bash
   git checkout -b feature/your-feature-name
   ```
3. Make your changes
4. Commit with a clear message:
   ```bash
   git commit -m "feat: add your feature description"
   ```
5. Push to your fork and open a Pull Request against `develop`

## Commit Message Format

Use the following prefixes:

| Prefix | When to use |
|---|---|
| `feat:` | New feature |
| `fix:` | Bug fix |
| `refactor:` | Code refactor |
| `style:` | CSS / UI changes |
| `docs:` | Documentation |
| `chore:` | Config, dependencies |

## Requirements

- PHP 8.2+
- Laravel 12
- Valid TouchEstate API credentials (see `.env.example`)

## Code Style

This project uses PHP CS Fixer. Before submitting a PR, run:

```bash
./vendor/bin/php-cs-fixer fix
```

## Reporting Issues

Use the [issue tracker](../../issues) to report bugs or request features. Please use the provided templates.

---

Developed by [Innovayse Digital Agency](https://innovayse.com)
