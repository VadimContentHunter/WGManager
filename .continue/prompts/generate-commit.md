---
name: Generate Commit
description: Generate a Conventional Commit message based on the current staged Git changes.
invokable: true
---

# Generate Commit

Analyze the current staged Git changes before generating the commit message.

Your task is to generate exactly one Git commit message.

The output must match the following format:

<type>(<scope>): <description>

or

<type>: <description>

The generated message must satisfy the following regular expression:

^(fix|feat|docs|style|refactor|test|chore)\s*(\([a-z0-9\-]+\))?\s*:\s[A-Za-z].*

## Commit type

Choose exactly one commit type.

| Type | Usage |
|------|-------|
| feat | Introduces new functionality |
| fix | Fixes a bug or incorrect behavior |
| docs | Documentation changes only |
| style | Formatting or style changes that do not affect behavior |
| refactor | Improves the code without changing behavior |
| test | Adds or modifies tests |
| chore | Maintenance tasks, tooling, CI, configuration, dependencies or build changes |

If multiple types are applicable, use the following priority:

1. feat
2. fix
3. refactor
4. docs
5. test
6. style
7. chore

## Scope

If the changes clearly belong to a specific module or component, include a scope.

Examples:

- auth
- api
- api-v2
- user
- order
- payment
- cache
- queue
- config
- console

Scope rules:

- lowercase only;
- letters, numbers and hyphens only;
- no spaces;
- no underscores.

If no meaningful scope exists, omit it.

## Description

The description must:

- be written in English;
- begin with a capital letter;
- use the imperative mood;
- describe the primary purpose of the change;
- be concise and specific;
- use only ASCII characters;
- not end with a period.

Describe the business or technical goal instead of implementation details.

Good examples:

feat(auth): Add JWT refresh token support

fix(payment): Prevent duplicate transaction processing

refactor(order): Simplify order status transitions

docs: Update installation guide

style: Format PHP files according to PSR-12

test(api): Add integration tests for user endpoint

chore: Update Composer dependencies

Bad examples:

fix: Fix bug

feat: New feature

refactor: Refactor code

style: Formatting

docs: Documentation

Update files

Various changes

Cleanup

## Analysis

Before generating the commit message:

1. Analyze all staged changes.
2. Identify the primary purpose of the commit.
3. Ignore formatting-only changes if functional changes exist.
4. Ignore reordered imports.
5. Ignore renamed variables.
6. Ignore generated files.
7. Ignore whitespace-only changes.
8. Ignore unrelated refactoring.
9. Base the commit message on the highest-level functional change.
10. Never invent changes that are not present.

## Output

Return only the commit message.

Do not:

- explain your reasoning;
- output Markdown;
- wrap the message in quotes;
- add comments;
- generate multiple commit messages;
- generate a commit body or footer.
