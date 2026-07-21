---
name: PHP Documentation
description: PHPDoc rules.
globs: "**/*.php"
---

# PHP documentation rules

When adding or editing PHP documentation, follow these rules and also respect the PHPDoc tag guidance in <https://github.com/php-fig/fig-standards/blob/master/proposed/phpdoc-tags.md>

## General documentation rules

- Write documentation only when it adds real value.
- Do not add redundant PHPDoc for code that is already obvious from the name and signature.
- If a method, property, or variable is self-explanatory, omit PHPDoc.
- If PHPDoc is included, it must explain the purpose of the element, not repeat what is already obvious from the code.

## Class documentation

- Every newly created class must have PHPDoc.
- The class docblock must at least explain the purpose or responsibility of the class.
- Keep the class description concise and useful.

## Methods, properties, and variables

- Add `@param`, `@var`, and `@return` for arrays and booleans always.
- For booleans, always describe the possible values as `true` and `false`.
- For arrays, always document generic structure.
- Array generics must be described up to 3 levels deep; beyond that, use `mixed`.
- For arrays, prefer explicit shapes or nested generics when helpful and readable.
- For booleans, always explain what `true` and `false` mean in context.

## When to omit tags

- Do not add `@param`, `@var`, or `@return` for obvious values when the purpose is already clear from the code.
- Exception: always document arrays and booleans.
- If a tag is present, it must include a short purpose description, not just the type.

## Method descriptions

- Include a method description when it is not obvious from the method name.
- Omit the description only when the method name already clearly and briefly explains the action.
- If the method action is hard to infer from the name, write a clear description.

## Examples of good PHPDoc behavior

- Prefer:
  - `@param array<string, UserDto> $users List of users indexed by identifier.`
  - `@return bool True when the cache is warm, false otherwise.`
- Avoid:
  - `@param array<string, UserDto> $users`
  - `@return bool`
  - PHPDoc that only repeats the code without explaining the purpose.
