CLAUDE.md - Project Guidelines & Workflow
This document serves as the primary source of truth for the Eneba project structure, development standards, and agent-based task management workflow.

🏗 Project Architecture
The project is divided into two main components and a specialized agent management system:

.agent/: The brain of the project. Contains task tracking, standard operating procedures, and active task status.
api/: Laravel 12 Backend (API only).
frontend/: Vue/Nuxt.js Frontend.
🤖 Agent Workflow (.agent/)
All development activities must be tracked through the .agent directory. This ensures the AI agent maintains context and follows historical fixes.

1. Task Management
Location: .agent/task/
Workflow:
todo/: New tasks or features with detailed technical requirements.
inprogress/: Active tasks. When work starts, the task file is moved here from todo.
done/: Completed task documentation for future reference.
Active Status: The .agent/README.md must always contain the currently active task details.
2. Standard Operating Procedures (SOP)
Location: .agent/sop/
Purpose: Every time a bug is fixed or a complex logic is implemented, a document must be created/updated here.
Learning: The agent must reference this folder to "learn" from previous mistakes and follow established fix patterns.
🛠 Backend Standards (api/)
A Laravel 12 API-only application.

Authentication: JWT Auth via Laravel Sanctum.
Database: PostgreSQL.
Pattern: Strict Model-Controller-Service architecture.
Controllers handle requests/responses.
Services contain business logic.
Models handle data relations and scopes.
Testing (E2E):
Use DatabaseTransactions trait (Do NOT use RefreshDatabase).
Tests must simulate real user requests and assert responses.
Code must be written to be fully testable via HTTP calls.
🎨 Frontend Standards (frontend/)
A Vue.js / Nuxt.js application.

Best Practices: Follow official Nuxt "Directory Structure" and "Auto-imports".
Documentation: Refer to components_documents.md and design_documents.md for UI/UX consistency.
State Management: Use Pinia (if needed) or Nuxt's native useState/useAsyncData hooks.
⌨️ Custom Commands
Available Claude commands in .claude/commands/:

tasks.md: List and manage current task queue.
bff-task.md: Specialized logic for backend-for-frontend synchronization.
fix.md: Guidelines for applying fixes based on SOP.
update-doc.md: Trigger to update internal documentation after a change.
