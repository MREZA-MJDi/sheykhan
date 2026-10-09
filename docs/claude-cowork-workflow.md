# Claude Cowork workflow for Sheykhan

This document makes the repository usable as a controlled hand-off to Claude Cowork / Claude-assisted development.

## Important distinction
Claude Cowork is a work agent that can work on files and multi-step tasks; it is not a Composer package or a runtime service that can be embedded into the Laravel site. This repository therefore does not send student data or production secrets to an AI service. Use Cowork only on a local, non-production checkout with the files and permissions you intentionally provide.

## Start a task
1. Open the local `sheykhan` checkout in Claude Desktop/Cowork.
2. Ask the agent to read `CLAUDE.md` first and then the relevant files under `docs/`, `routes/`, `app/`, `database/migrations/` and `tests/`.
3. Give one end-to-end acceptance goal at a time (for example, “upload a recording as a teacher and verify only enrolled students can watch it”).
4. Require the agent to identify the migration, authorization rule, test and rollback implications before editing.
5. Allow edits on a feature branch only. Do not connect a Cowork task to production `.env`, server SSH keys, payment secrets, or live student records.
6. Review the diff and ask for the exact commands/tests run. The final acceptance evidence must come from CI or a local test run, not from a statement that code “looks good”.

## Suggested acceptance prompt
> Read `CLAUDE.md` and `docs/claude-cowork-workflow.md`. Work only on a feature branch. First report the current implementation and the relevant schema. Make one cohesive implementation with regression tests. Do not invent payment success, consent, student media, or production data. Run the test/build commands you can execute; report commands that could not be run. Do not deploy to production or modify `main`.

## What must stay out of Cowork context
- Production `.env`, database exports, ID numbers, student records, guardian contact details, payment credentials, server private keys and unredacted logs.
- Do not upload legal documents containing private signatures or voice/video releases unless the organization has approved that data handling.
