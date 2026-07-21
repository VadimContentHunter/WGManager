---
name: PHP PSR-12
description: PSR-12 formatting and style rules.
globs: "**/*.php"
---

# PHP code style

For PHP files, always follow PSR-12.

## Formatting

- Use 4 spaces for indentation.
- Never use tabs.
- Keep one statement per line.
- Use a trailing newline at the end of each file.
- Prefer readable line lengths.
- Keep brace placement consistent with PSR-12.
- Avoid unnecessary blank lines.

## Structure

- Start PHP files with `<?php`.
- Use namespaces.
- Import classes with `use` statements when appropriate.
- Prefer one class, interface, trait, or enum per file.
- Prefer typed properties, parameters, and return values.
- Use `declare(strict_types=1);` for new files unless the project already clearly avoids it.

## Code quality

- Keep methods small and focused.
- Prefer early returns over deep nesting.
- Avoid duplicated logic.
- Prefer dependency injection over creating dependencies inline.
- Preserve existing project conventions when they conflict with generic style rules.