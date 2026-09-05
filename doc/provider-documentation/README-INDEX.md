# Public Markdown Documentation Portal

This folder now includes `index.php`, a public documentation landing page.

## How it works

- Automatically discovers every `.md` file in the same directory.
- Uses `README.md` as the default landing document when available.
- Renders headings, paragraphs, emphasis, links, images, lists, blockquotes, horizontal rules, tables, and fenced code blocks.
- Generates an "On this page" table of contents from Markdown headings.
- Provides client-side documentation search.
- Provides copy buttons for fenced code blocks.
- Responsive on desktop, tablet, and mobile.
- No Composer package, Node.js build, database, or external CDN is required.
- New Markdown files appear automatically in the navigation.

## Deployment

Upload the contents of this directory to your existing:

`doc/provider-documentation/`

Then open:

`/doc/provider-documentation/`

The server must allow PHP execution for `index.php`.

## Security

The renderer escapes Markdown text before converting supported Markdown constructs to HTML. Links are restricted to normal HTTP(S), mailto, relative, root-relative, and fragment URLs. Do not place executable server-side code in Markdown files.

## Updating documentation

Simply add or update `.md` files in this directory. The landing page reads them at request time, so no manual index update is necessary.
