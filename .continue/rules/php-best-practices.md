---
name: PHP Best Practices
description: General PHP development best practices.
globs: "**/*.php"
---

# PHP Best Practices

When generating or modifying PHP code, follow these rules.

## General

- Write clean, readable, and maintainable code.
- Preserve the existing project architecture and coding style.
- Modify only the code required to complete the requested task.
- Do not introduce unrelated refactoring unless explicitly requested.
- Prefer clarity over cleverness.

## SOLID

- Follow SOLID principles.
- Keep classes focused on a single responsibility.
- Keep methods focused on a single task.
- Prefer composition over inheritance.
- Depend on abstractions instead of implementations.

## Methods

- Keep methods short and easy to understand.
- Prefer early return instead of nested conditions.
- Avoid unnecessary `else` blocks after `return`, `throw`, or `continue`.
- Split complex logic into private methods when it improves readability.
- Avoid methods with too many parameters.

## Dependencies

- Prefer dependency injection.
- Do not instantiate services inside business logic unless required by the framework.
- Avoid static state.
- Avoid global state.

## Types

- Always use parameter types.
- Always use return types.
- Always use property types.
- Avoid `mixed` whenever possible.
- Avoid `callable` when a dedicated interface is more appropriate.
- Prefer value objects over primitive obsession when appropriate.

## Variables

- Use meaningful variable names.
- Keep variable scope as small as possible.
- Avoid unnecessary temporary variables.
- Do not reuse variables for different purposes.

## Conditions

- Keep conditions simple.
- Extract complex boolean expressions into well-named private methods.
- Prefer positive conditions over double negation.
- Use `match` instead of large `switch` statements when appropriate.

## Collections

- Prefer array functions (`array_map`, `array_filter`, `array_reduce`, etc.) when they improve readability.
- Do not create unnecessary intermediate arrays.
- Prefer explicit array shapes when the structure is fixed.

## Exceptions

- Throw exceptions only for exceptional situations.
- Use specific exception classes.
- Never silently swallow exceptions.
- Add context to exception messages when it helps debugging.

## Modern PHP

When supported by the project PHP version, prefer modern language features:

- constructor property promotion;
- readonly properties;
- enums;
- match expressions;
- nullsafe operator;
- named arguments when they improve readability;
- first-class callable syntax when appropriate.

## Performance

- Do not optimize prematurely.
- Prefer readable code unless profiling proves otherwise.
- Avoid unnecessary object creation.
- Avoid duplicate computations.

## Security

- Never trust external input.
- Validate data as close as possible to the entry point.
- Escape output according to its destination.
- Never build SQL queries through string concatenation.
- Prefer prepared statements or ORM query builders.

## Final check

Before finishing:

- Remove dead code.
- Remove commented-out code.
- Remove unused imports.
- Remove unused variables.
- Ensure the code is consistent with the surrounding codebase.
