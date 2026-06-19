# Phase 0: Research — Experiment Reporting & Documentation System

**Date**: 2026-06-19
**Spec**: SPEC-004 (experiment-reporting)
**Plan**: [plan.md](plan.md)

## 1. PDF Generation Library

### Selected: `barryvdh/laravel-dompdf`

**Rationale**:
- **Zero external binary dependencies**: Works entirely in PHP, no need for wkhtmltopdf, Puppeteer, or Node.js
- **Compatible with Docker**: PHP 8.2 + GD already installed (see `backend/Dockerfile`)
- **Proven in Laravel ecosystem**: 7k+ stars, active maintenance, dedicated Laravel wrapper
- **PNG embedding**: dompdf supports `<img src="...">` with local file paths and base64 data URIs
- **Unicode/CJK**: Handles UTF-8 via embedded fonts (DejaVu)
- **CA-06 compliance**: dompdf generation for a single-page report is typically < 5s on the target hardware

**Alternatives considered**:

| Library | Requires external binary | Docker compatible | Decision |
|---------|--------------------------|-------------------|----------|
| barryvdh/laravel-dompdf | No | ✅ Yes | **Selected** |
| barryvdh/laravel-snappy | Yes (wkhtmltopdf) | ❌ Complex | Rejected |
| spatie/laravel-pdf | Yes (Browsershot/Puppeteer) | ❌ Heavy | Rejected |
| mpdf/mpdf | No (pure PHP) | ✅ Yes | Good alt, but no Laravel wrapper; dompdf preferred |

**Constraints**:
- dompdf CSS support is limited — use tables, inline styles, avoid complex layouts
- Font support: DejaVu fonts bundled; for small text use 8pt+
- PNG images must be local filesystem paths or base64

## 2. Markdown Rendering in Next.js

### Selected: `react-markdown` + `remark-gfm`

**Rationale**:
- Standard approach for rendering Markdown in React/Next.js
- `remark-gfm` adds GitHub Flavored Markdown (tables, task lists, strikethrough)
- Works with TailwindCSS prose classes for typography
- No build-time transforms needed (experiment reports are dynamic content)

**Installation**:
```bash
npm install react-markdown remark-gfm
```

**Usage pattern** (server component or client component):
```tsx
import ReactMarkdown from 'react-markdown';
import remarkGfm from 'remark-gfm';

<ReactMarkdown remarkPlugins={[remarkGfm]}>{markdownContent}</ReactMarkdown>
```

## 3. Experiment Metadata Format

### Selected: JSON sidecar file (`experiment.json`)

**Rationale**:
- PHP parses JSON natively (no YAML parser dependency required)
- Clear separation between narrative content (report.md) and structured metadata (experiment.json)
- Easier to validate and version than YAML front-matter
- Compatible with the existing Laravel casts (`'array'`)

**Schema**: See [data-model.md](data-model.md#experiment-json-schema)

## 4. Experiment Directory Structure

```text
experiments/
├── NNN-nombre-experimento/
│   ├── experiment.json    # Structured metadata (required)
│   ├── report.md          # Informe experimental narrativo (required)
│   ├── report.pdf         # PDF generado a partir de report.md (optional, generated)
│   └── assets/            # Archivos adjuntos (optional)
│       ├── evaluation-2-map-overview.png
│       ├── evaluation-2-report.pdf
│       └── ...
```

**Naming convention**: `NNN-kebab-case-name` where NNN is a 3-digit sequential ID.

## 5. Reproducibility Metadata (PDF)

Every evaluation PDF must embed:

```json
{
  "algorithm": "manual-asignacion",
  "algorithm_version": "1.0",
  "dataset": "Valparaíso Demo",
  "evaluation_id": 2,
  "generated_at": "2026-06-19T15:30:00-04:00",
  "spec_version": "SPEC-004"
}
```

`spec_version` is a fixed constant: `"SPEC-004"` for this spec version.

## 6. sync:experiments Command

An artisan command (`experiments:sync`) will scan the `experiments/` directory and sync with the database:

1. Read `experiments/` directory entries
2. For each, read `experiment.json`
3. Create/update the `experiments` DB row
4. Link evaluations listed in `evaluation_ids`
5. Report changes (created, updated, deleted)

This command is run manually after creating/editing experiment directories.

## 7. Frontend Architecture Decisions

- **Experiments**: New section in main navigation
- **Docs**: Accessible via a "Docs" link (Guía de métricas, Documentación de pantallas)
- **PDF download**: Button on evaluation detail page calling `GET /evaluations/{id}/pdf`
- **No state management library**: Use React state + fetch, same as existing pattern

## 8. Risks and Mitigations

| Risk | Likelihood | Impact | Mitigation |
|------|-----------|--------|------------|
| dompdf cannot render complex tables | Medium | Medium | Test early with real data; use simple table layouts |
| PDF generation > 10s for large datasets | Low | High | Cache generated PDFs; serve cached version if exists |
| Markdown > 2000 lines crashes react-markdown | Low | Medium | Limit rendering to main content area with overflow |
| Experiment directory structure changes manually | Medium | Medium | sync command validates structure on each run |
