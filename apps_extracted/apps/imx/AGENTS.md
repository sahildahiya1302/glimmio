# AGENTS.md — Glimmio Influencer Marketing Platform (IMX)

**Goal:** Convert the current IMX project into a secure, scalable, production‑grade influencer marketing platform with server‑rendered EJS views, real‑time updates, and a full feature set for Brands, Influencers, and Admins. This plan defines specialized “agents” (workstreams) with detailed tasks, deliverables, acceptance criteria, sequencing, and security baselines.

---

## 0) North Star & Principles

* **Security-first**: Least privilege, defense-in-depth, zero-trust for services, secure defaults, privacy by design.
* **Performance & UX**: Sub‑200ms TTFB for most pages, lighthouse ≥ 90, responsive across mobile/desktop.
* **Modularity**: Layered architecture with clear boundaries: Web (EJS), API, Services, Workers, Integrations.
* **Observability**: Logs, metrics, traces from day one; meaningful dashboards and alerts.
* **Incremental delivery**: Ship in phases with feature flags and A/B rollouts.
* **Data ownership & compliance**: Align with India’s DPDP Act + global best practices; auditable consent & access logs.

---

## 1) Platform Overview (Target Feature Set)

### 1.1 Roles & Tenancy

* **Roles**: Brand, Influencer, Agency (optional), Admin, Finance, Support.
* **Tenancy**: Soft multi‑tenant via `org_id` scoping for Brands/Agencies, influencers can belong to zero/one/many org workspaces.
* **RBAC/ABAC**: Permission matrix for actions (campaign CRUD, budgets, payouts, messaging, approvals, analytics). Attribute checks for campaign visibility and data export.

### 1.2 Core Modules

* **Campaigns**: Briefs, goals, deliverables, timelines, content specs, usage rights, ad-usage permissions, whitelisting.
* **Discovery**: Influencer search (filters: category, followers, ER, geo, language), shortlists, outreach workflows.
* **Applications & Matching**: Influencers apply; Brands shortlist; auto-match suggestions.
* **Contracts**: Standardized agreements (barter/paid), negotiation, e‑sign, versioning.
* **Messaging/DM**: 1:1 and campaign-room chat, attachments, typing/presence, read receipts, quick replies, templates.
* **Content Workflow**: Ideas → Drafts → Approvals → Publishing tracking (with platform links), revision cycles, checklists.
* **Delivery Proofs**: Links, screenshots, reels/posts metadata via API integrations, automatic validation.
* **Payments & Escrow**: Milestone‑based payouts, invoices, TDS/GST fields, reconciliation, payouts to influencers.
* **Analytics**: Campaign KPIs (reach, impressions, CTR, saves, shares), ROI, cost per result, creative breakdowns, cohort views.
* **Reporting**: Live dashboards, PDF/CSV exports, scheduled email reports, shareable read‑only links.
* **Compliance & Audit**: Consent logging, change history, IP/UA logs, suspicious-activity flags.
* **Support & SLA**: Ticketing, escalations, response/resolve time tracking, macros, CSAT.

### 1.3 Integrations (Phase‑gated)

* **Meta/Instagram Graph API**: Permissions for insights, content validation, branded content ads; webhooks for post/reel updates.
* **YouTube Data API** (phase 2), **TikTok** (phase 3) where permissible.
* **Email/SMS/WhatsApp**: Transactional (post approvals, milestones, payouts). Providers: SES/Sendgrid, Twilio/MSG91, WhatsApp Cloud.
* **Payments**: Razorpay/Stripe, bank payouts via Payout APIs; PAN/GST capture for invoices; automatic TDS computation (India).
* **Storage**: S3-compatible for media, signed URLs, thumbnails, transcoding jobs for video previews.
* **Search**: Meilisearch/Elasticsearch for discovery & logs.

---

## 2) Architecture Baseline

* **Server**: Node.js (LTS) + Express.
* **Views**: **EJS** with layouts, partials, and view helpers. Progressive enhancement with htmx or Alpine.js where needed. (Optional “islands” with React for highly interactive modules later.)
* **API**: REST (JSON) with versioning `/api/v1/*`. Consider GraphQL later for analytics aggregation.
* **DB**: PostgreSQL (primary), Redis (caching, sessions, rate limits, queues), S3 storage.
* **ORM**: Prisma (prefer) or Sequelize/TypeORM. Prisma for typed schemas & migrations.
* **Workers**: BullMQ/Agenda for jobs (ingest metrics, send notifications, generate reports).
* **Real‑time**: Socket.IO with Redis adapter for multi‑instance.
* **Infra**: Nginx reverse proxy, PM2 (dev) → systemd/Kubernetes (prod), CDN (Cloudflare) for static/media.
* **Secrets**: `.env` for dev, Vault/SSM in prod; no secrets in repo.

**Folder Layout (monorepo)**

```
/ imx
  /apps
    /web          # Express + EJS (SSR)
    /api          # Express REST API (JWT + session)
    /worker       # BullMQ workers & schedulers
  /packages
    /db           # Prisma schema + migrations + generated client
    /shared       # shared utils, DTOs, validation, feature flags
    /ui           # shared EJS partials, helpers, assets
  /infra          # IaC, Docker, Nginx, CI/CD, Helm charts (future)
```

---

## 3) Migration to EJS (Agent: **Frontend/EJS Agent**)

**Objectives**

* Convert current templates/pages to EJS with a clean layout system.
* Reuse components via partials; create helper library for formatting, icons, and form controls.
* Ensure CSP-friendly, minimal inline JS; progressive enhancement only.

**Tasks**

1. **Enable EJS** in Express web app: `app.set('view engine', 'ejs')`, `views/`, `partials/`, `layouts/`.
2. **Create Layouts**: `layouts/base.ejs` (HTML shell, CSP meta, header, sidebar, footer slots), `layouts/auth.ejs`.
3. **Partials**: navbar, sidebar, breadcrumbs, flash, pagination, modals, toasts, form controls, table components.
4. **View Helpers**: currency, date/time (TZ aware), number formatting, permission checks, feature flags.
5. **Pages**: Dashboard, Campaigns (list/detail/edit), Influencer Search, Applications, Messages, Contracts, Payments, Reports, Settings.
6. **Client Enhancements**: htmx for inline updates (approvals, status toggles), Alpine.js for simple stateful UI (modals, tabs), Socket.IO for real‑time (messages, live metrics).
7. **Accessibility**: semantic HTML, keyboard support, ARIA for modals, color contrast.

**Deliverables**

* EJS component library (`/packages/ui`) + usage docs.
* Page‑by‑page parity checklist.

**Acceptance Criteria**

* All current pages render via EJS with shared layouts/partials.
* Lighthouse ≥ 90 (desktop) on critical flows.

---

## 4) Security Hardening (Agent: **Security Agent**)

**Objectives**

* Establish a hardened baseline across auth, data, network, and app layers.

**Tasks**

1. **Auth**:

   * Sessions (server‑side) with Redis store; `SameSite=Lax`, `HttpOnly`, `Secure` cookies.
   * Optional short‑lived JWT for API with rotation & audience/issuer checks.
   * Argon2id password hashing; login throttling; 2FA (TOTP), backup codes.
   * SSO (Google/Microsoft) optional for Brand/Admin.
2. **RBAC/ABAC**: Central policy engine; middleware for route‑level checks; permission cache in Redis.
3. **Input Validation**: Zod/Yup DTOs; strict schemas for all controllers; centralized error mapper.
4. **Headers & CSP**: Helmet presets; CSP default‑src 'self'; disallow inline scripts; nonces for allowed scripts; block mixed content.
5. **Rate Limiting & DDoS**: IP+user+route limits; Redis sliding window; Nginx global limits; surge queue.
6. **Upload Security**: MIME/extension validation, AV scan (ClamAV), image re‑encode with sharp, EXIF strip, size caps.
7. **Secrets & Config**: No secrets in repo; rotate keys; principle of least privilege for DB/service accounts.
8. **Webhooks**: HMAC signature verification, replay protection (nonce+timestamp), idempotency keys.
9. **Audit & Logs**: Tamper‑evident audit table; admin actions; auth events; PII redaction in logs; request IDs.
10. **Payments & PII**: Never store card data; encrypt sensitive fields at rest (KMS envelope); data retention policies.
11. **Infra**: Fail2ban, UFW firewall, OS patching; read‑only FS for containers where possible.
12. **Vuln Mgmt**: Dependabot/Snyk; weekly scans; SBOM generation.

**Deliverables**

* Security baseline doc + checklists.
* Pen-test plan & test users.

**Acceptance Criteria**

* OWASP ASVS L2 coverage; zero critical findings in baseline scan.

---

## 5) Data & Schema (Agent: **Data/Schema Agent**)

**Objectives**

* Define normalized schemas for core entities and events.

**Core Entities (indicative)**

* `users` (id, org\_id?, role, email, phone, password\_hash, 2fa\_secret, status, last\_login\_at)
* `orgs` (id, type: brand/agency, billing\_profile\_id, settings)
* `influencers` (id, user\_id?, public\_profile fields, categories, languages, rates, social\_handles)
* `campaigns` (id, org\_id, title, brief, goals, budget, currency, status, timeline, visibility)
* `campaign_deliverables` (id, campaign\_id, type, platform, specs, due\_at, status)
* `applications` (id, campaign\_id, influencer\_id, status, proposal, rates)
* `contracts` (id, campaign\_id, influencer\_id, json\_terms, signed\_at\_brand, signed\_at\_influencer)
* `messages` (id, thread\_id, sender\_id, body, attachments, read\_at)
* `threads` (id, scope: campaign/1:1, participant\_ids)
* `payments` (id, contract\_id, amount, tax\_fields, status, provider\_ref)
* `payouts` (id, influencer\_id, amount, bank\_ref, status)
* `insights` (id, post\_ref, metrics\_json, fetched\_at)
* `files` (id, owner\_id, path, mime, size, sha256)
* `audit_logs` (id, actor\_id, action, entity, entity\_id, diff\_json, ip, ua, ts)

**Event Tables** (append‑only for analytics)

* `events_app` (actor\_id, type, context\_json, ts)
* `events_metrics` (campaign\_id, influencer\_id, metric\_key, metric\_value, ts)

**Deliverables**

* Prisma schema, migration scripts, seed data, ERD diagram (markdown).

**Acceptance Criteria**

* Migrations apply cleanly; referential integrity; ≥ 90% queries covered by indexes.

---

## 6) API Design (Agent: **API Agent**)

**Objectives**

* Consistent, well‑documented REST API.

**Standards**

* Versioned: `/api/v1/*`.
* Auth: session or JWT (opaque tokens acceptable), CSRF on state‑changing forms.
* Validation: request/response schemas; typed DTOs.
* Errors: RFC 7807 `application/problem+json`.
* Idempotency on POST (idempotency-key header).

**Endpoints (selection)**

* Auth: `POST /auth/login`, `POST /auth/2fa/verify`, `POST /auth/logout`.
* Users: `GET/POST /users`, `PATCH /users/:id`, `POST /users/:id/role`.
* Campaigns: `GET/POST /campaigns`, `GET /campaigns/:id`, `PATCH /campaigns/:id`, `POST /campaigns/:id/publish`.
* Applications: `POST /campaigns/:id/apply`, `PATCH /applications/:id/status`.
* Contracts: `POST /contracts`, `POST /contracts/:id/sign`.
* Messaging: `GET /threads`, `POST /threads`, `POST /threads/:id/messages`.
* Files: `POST /files/upload` (signed URL), `GET /files/:id` (scoped).
* Payments/Payouts: `POST /contracts/:id/invoice`, `POST /payouts`, webhook endpoints.
* Insights: `POST /integrations/meta/webhook`, `GET /insights/:id`.

**Docs**

* OpenAPI YAML + Redoc UI at `/docs`.

**Acceptance Criteria**

* Contract tests pass; 100% endpoints documented; security tests for auth/CSRF.

---

## 7) Real‑Time (Agent: **Realtime Agent**)

**Objectives**

* Bi‑directional updates for chat, approvals, activity feeds, and live metrics.

**Tasks**

1. Socket.IO setup with Redis adapter; namespace per org; rooms per campaign/thread.
2. Presence (online, typing), read receipts, notifications queue fallback to email.
3. Live metrics: ingest insights → aggregate → push to dashboard via sockets.
4. Reliability: join/leave events, reconnect logic, backpressure handling, rate limiting.

**Deliverables**

* Socket events spec, server + client wiring, test harness.

**Acceptance Criteria**

* ≤ 2s end‑to‑end latency for updates on typical loads; soak tests pass.

---

## 8) Payments & Finance (Agent: **Payments Agent**)

**Objectives**

* Escrow‑style milestone payments, invoicing, taxes, payouts.

**Tasks**

1. Provider integration (Razorpay/Stripe) with order creation, capture, refunds.
2. Tax fields (GST, PAN), TDS auto‑calc for influencer payouts (India context), downloadable invoices.
3. Wallet/escrow ledger with double‑entry accounting model; reconciliation jobs.
4. Payouts via provider APIs; status webhooks; failure retries.

**Deliverables**

* Finance data model, accounting entries, invoice PDF templates.

**Acceptance Criteria**

* All money movements audited; idempotent webhook handling; exact ledger balance.

---

## 9) Messaging & Content (Agent: **Messaging Agent**)

**Objectives**

* Secure chat + content approvals workflow.

**Tasks**

1. Threads (campaign, 1:1); mentions; file attachments with virus scan; link unfurling.
2. Content lifecycle: draft → review → approve → deliver; version history; comment threads on assets.
3. Templates & quick replies; canned “approval with notes”.

**Deliverables**

* EJS chat UI components; moderation hooks; storage policies.

**Acceptance Criteria**

* Files safely handled; no XSS/SSRF vectors; smooth UX under poor networks.

---

## 10) Influencer Discovery (Agent: **Discovery Agent**)

**Objectives**

* Fast, rich search and shortlist management.

**Tasks**

1. Index influencers in Meilisearch/Elasticsearch; facets: category, follower bands, ER, location, language.
2. Importers: CSV upload; API sync from Meta (consented) to enrich profiles.
3. Shortlists: save lists, share read‑only link, export CSV.

**Deliverables**

* Search indexer worker, facet config, discovery UI.

**Acceptance Criteria**

* P95 search < 300ms; accurate facet counts; debounced query UX.

---

## 11) Integrations (Agent: **Integrations Agent**)

**Objectives**

* Robust Meta/Instagram integration + webhook lifecycle.

**Tasks**

1. App setup: permissions, review notes; secure redirect URIs.
2. OAuth flows, token refresh, storage (encrypted), minimal scopes.
3. Webhooks: page/IG events → verify signatures → dedupe → queue → process.
4. Rate limit handling + backoff; retries with DLQ.

**Deliverables**

* Integration guide, secrets rotation playbook.

**Acceptance Criteria**

* Zero dropped webhook events in chaos tests; compliant permission usage.

---

## 12) Analytics & Reporting (Agent: **Analytics Agent**)

**Objectives**

* Actionable campaign analytics and automated reports.

**Tasks**

1. Metrics model: reach, impressions, views, ER, CTR, CPC/CPM/CPA, ROAS.
2. Aggregations: daily snapshots; creative breakdowns; UTM capture.
3. Dashboards: campaign view, org overview, influencer performance; export CSV/PDF.
4. Scheduled reporting: email with links, access‑controlled.

**Deliverables**

* Precomputed tables, worker jobs, EJS charts (Recharts via pre-rendered SVG or client hydration).

**Acceptance Criteria**

* Data correctness checked vs raw; reports generate under 30s for big campaigns.

---

## 13) Moderation & Trust (Agent: **Trust & Safety Agent**)

**Objectives**

* Keep the platform safe and compliant.

**Tasks**

1. Content moderation: profanity filters, image safety checks; manual review queue.
2. Fraud detection: velocity rules, duplicate device signatures, unusual payout patterns.
3. Onboarding KYC: Influencer PAN/bank validation; Brand GST verification (where applicable).
4. Policy center: ad disclosures, content policies, strikes & enforcement.

**Deliverables**

* Rule engine (JSON rules), reviewer tools.

**Acceptance Criteria**

* False positive/negative rates monitored; appeals flow present.

---

## 14) Docs, Help, & Onboarding (Agent: **Docs Agent**)

**Objectives**

* Clear guides and in‑app walkthroughs.

**Tasks**

1. Product docs: getting started, campaign creation, payments, reports, integrations.
2. In‑app tours (intro.js/driver.js), empty‑state guides, tooltips.
3. Email lifecycle: welcome, activation, first campaign tutorial, tips.

**Deliverables**

* `/docs` site, in‑app tour configs, email templates.

**Acceptance Criteria**

* Time‑to‑first‑campaign < 15 minutes for a new Brand.

---

## 15) DevOps, CI/CD & Observability (Agent: **DevOps Agent**)

**Objectives**

* Make deployments boring and rollbacks instant.

**Tasks**

1. CI: lint, typecheck, unit/integration tests, Prisma migrate, SBOM.
2. CD: blue/green or canary; env promotion; rollback button.
3. Containerization: Dockerfiles (non‑root), multi‑stage builds; health checks.
4. Nginx: TLS (Let’s Encrypt), HTTP/2, gzip/brotli, static caching.
5. Monitoring: Prometheus/Grafana or cloud APM (Datadog/New Relic); alerting on SLO breaches.
6. Backups: daily Postgres snapshots; restore runbook.

**Deliverables**

* CI/CD pipelines, dashboards, runbooks.

**Acceptance Criteria**

* MTTR < 15 min; RPO ≤ 24h; SLO: 99.9%.

---

## 16) QA & Testing (Agent: **QA Agent**)

**Objectives**

* Prevent regressions; guarantee critical paths.

**Tasks**

1. Unit tests for services; API contract tests (Pact or supertest); e2e smoke (Playwright).
2. Security tests: auth bypass, CSRF, IDOR, SSRF.
3. Load tests: k6 for chat and dashboards; spike tests for webhooks.
4. UAT scripts; bug triage flow; release checklist.

**Deliverables**

* Test coverage reports; e2e specs for high‑value flows.

**Acceptance Criteria**

* > 80% coverage on core services; all critical scenarios automated.

---

## 17) Performance (Agent: **Performance Agent**)

**Objectives**

* Keep it fast under load.

**Tasks**

1. Query budgets; N+1 detection; caching (Redis) for hot reads; HTTP caching headers.
2. Asset pipeline: minify, tree‑shake, image optimization, lazy load.
3. DB tuning: connection pooling, prepared statements.

**Deliverables**

* Perf budgets, flamegraph profiles, cache keys map.

**Acceptance Criteria**

* P95 TTFB < 300ms for key pages; API P95 < 250ms.

---

## 18) Feature Flags & Rollouts (Agent: **Flags Agent**)

**Objectives**

* Safe experiments and gradual releases.

**Tasks**

1. Implement flags (Unleash/ConfigCat/open‑source) with SDK in web/api.
2. Targeting by org/role; kill switches; config UI for Admin.

**Deliverables**

* Flags service, admin panel controls.

**Acceptance Criteria**

* New features shipped behind flags; instant disable works.

---

## 19) Growth & Marketing (Agent: **Growth Agent**)

**Objectives**

* Self‑serve acquisition and retention.

**Tasks**

1. Pricing plans, trials; upgrade paths; usage limits; paywall UI.
2. Referral links, invite flows; coupon codes.
3. Email sequences; churn detection; NPS/feedback widgets.

**Deliverables**

* Billing pages, referral system, analytics for funnel.

**Acceptance Criteria**

* Measurable activation rate improvement; churn alerts firing.

---

## 20) Accessibility & Localization (Agent: **A11y/L10n Agent**)

**Objectives**

* Inclusive and global-ready.

**Tasks**

1. i18n keys in EJS; RTL readiness; locale switcher.
2. WCAG 2.1 AA audit; keyboard navigation; focus states; skip links.

**Deliverables**

* Translation JSONs; a11y report.

**Acceptance Criteria**

* Passes automated checks; manual spot checks OK.

---

## 21) Detailed Phase Plan (Sequenced)

**Phase 0 — Baseline Setup (Week 1)**

* Repo restructure; add `/apps/web` (EJS), `/apps/api`, `/apps/worker`, Prisma in `/packages/db`.
* Add Helmet, rate limits, session store, CSRF middleware.
* CI basics: lint+test.

**Phase 1 — EJS Migration & Auth (Weeks 2‑3)**

* Layouts, partials, first 5 key pages; login/signup + 2FA; RBAC skeleton.
* Seed data + fixtures for demo org/brand/influencer.

**Phase 2 — Campaigns & Messaging (Weeks 4‑6)**

* Campaign CRUD; deliverables; applications; contracts draft.
* Chat v1 (Socket.IO), file uploads with AV scan and S3.

**Phase 3 — Integrations & Analytics (Weeks 7‑9)**

* Meta/IG OAuth + webhook; insights ingestion workers.
* Analytics dashboards; scheduled reports.

**Phase 4 — Payments & Payouts (Weeks 10‑12)**

* Escrow ledger; invoicing; payouts; tax/GST/TDS fields.

**Phase 5 — Discovery & Moderation (Weeks 13‑14)**

* Influencer search (Meilisearch); shortlists; moderation tools.

**Phase 6 — Hardening & Launch (Weeks 15‑16)**

* Load/security testing; pen‑test fixes; A/B flags; docs; runbooks; SLA instrumentation.

---

## 22) Acceptance Criteria per Milestone

* **M1**: Users can sign in with 2FA; EJS pages live; baseline security headers; sessions in Redis.
* **M2**: Create & manage campaigns; influencers apply; messaging works; files safe.
* **M3**: IG data ingested; dashboards show live metrics; exports.
* **M4**: Payments operational end‑to‑end; ledger balances to provider totals; invoices downloadable.
* **M5**: Discovery functional with fast filters; moderation queue live.
* **M6**: 99.9% SLO; CI/CD with one‑click rollback; comprehensive docs/support center.

---

## 23) UX Blueprints (EJS Structure)

* `views/layouts/base.ejs` — header (org switcher, search), left nav (Dashboard, Campaigns, Discovery, Messages, Reports, Billing, Settings), global toasts, footer.
* `views/campaigns/index.ejs` — filters (status, date, budget), table cards, quick actions (approve, duplicate), htmx partial refresh.
* `views/campaigns/show.ejs` — tabs: Overview, Deliverables, Messages, Influencers, Finance, Insights.
* `views/discovery/index.ejs` — search bar, facet sidebar, grid with cards (ER, last 30d posts, rates).
* `views/messages/thread.ejs` — message list, composer with attachments, presence indicators.
* `views/reports/index.ejs` — big numbers, trend charts, export buttons.
* `views/settings/*` — org settings, users & roles, integrations, billing.

---

## 24) Security Checklist (Implement & Verify)

* [ ] Argon2id hashes; minimum password entropy; breach password check.
* [ ] 2FA with backup codes; enforced for Admin.
* [ ] Session fixation prevention; rotate on privilege change.
* [ ] CSRF tokens on all forms; double‑submit cookie strategy.
* [ ] Input validation (server) + output escaping (EJS) → no reflected/stored XSS.
* [ ] Parameterized queries; ORM prepared statements; no raw SQL without review.
* [ ] File uploads: AV, size limits, mime validation, image re-encode, signed URLs.
* [ ] Rate limiting: login, password reset, messaging, webhook ingress.
* [ ] Secure headers: HSTS, CSP, Referrer‑Policy, Permissions‑Policy.
* [ ] Webhook HMAC validate; idempotent processing.
* [ ] Audit logs for admin & finance actions; tamper‑evident.
* [ ] Backups tested restore; disaster recovery runbook.

---

## 25) Observability Dashboards

* **API**: RPS, latency (p50/p95), error rate, top endpoints, rate limit hits.
* **Workers**: queue depth, processing time, retries, DLQ size.
* **DB**: slow queries, connections, replication lag (if any).
* **Business**: new campaigns/day, applications, approvals, payout volume.

---

## 26) Runbooks & Playbooks

* **Incident Response**: severity levels, on‑call rotations, communication templates.
* **Key Rotation**: steps for providers and internal tokens.
* **Data Export/Delete**: per DPDP requests; SLA and verification.
* **Webhook Outage**: backfill strategies from provider APIs.

---

## 27) Risks & Mitigations

* **API quota limits** → Caching, scheduled pulls, adaptive backoff.
* **Data integrity** → Ledger model for money, idempotent jobs, checksums on files.
* **Vendor lock‑in** → Abstracted provider modules, feature flags for provider swap.
* **Scaling chat** → Redis cluster, sharded rooms, message retention policies.

---

## 28) Deliverables Summary

* EJS front‑end with component library & accessibility.
* Hardened API with RBAC/ABAC, audits, and tests.
* Real‑time messaging & live metrics.
* Payments, invoices, payouts, and ledger.
* Discovery search with facets.
* Integrations with Meta/IG + webhooks.
* Analytics dashboards + scheduled reports.
* CI/CD, observability, docs, runbooks.

---

## 29) Next Steps (Immediate To‑Dos)

1. Confirm DB choice (Postgres + Prisma) and run initial migration.
2. Scaffold `/apps/web` with EJS layouts & partials; migrate login + dashboard first.
3. Add Redis + session + CSRF + Helmet.
4. Define RBAC matrix and seed roles/permissions.
5. Choose queue (BullMQ) and set up worker skeleton.
6. Draft integration app with Meta (app IDs, redirect URIs) for phase 3.

---

## 30) Appendices

**A. Permission Matrix (excerpt)**

* Brand Admin: CRUD campaigns, invite users, approve content, finance view.
* Brand Member: Create drafts, chat, view reports.
* Influencer: View assigned campaigns, submit content, invoices, chat.
* Finance: View/pay invoices, payouts.
* Support: Read‑only + ticket tools.
* Platform Admin: Superuser (segregated environment, audited).

**B. Data Retention (defaults)**

* Logs 90 days, audits 1 year, financial 7 years, assets 1 year post‑campaign (configurable).

**C. Tech Versions**

* Node LTS, Express 5+, Prisma latest, Postgres 14+, Redis 7+, Meilisearch stable.

**D. Coding Standards**

* TypeScript in API/workers; ESLint+Prettier; commit message conventions; PR templates.

---

> This AGENTS.md is a living document. Update per sprint with status, newly discovered risks, and architectural decisions (ADRs).
