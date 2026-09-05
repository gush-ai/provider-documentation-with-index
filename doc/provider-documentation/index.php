<?php
declare(strict_types=1);

/**
 * Public Markdown Documentation Portal
 * Place this index.php inside doc/provider-documentation/.
 *
 * It automatically discovers every .md file in this directory, so adding a
 * new Markdown document does not require editing this file.
 */

$docsDir = __DIR__;
$docs = [];

function safeSlug(string $value): string {
    $value = strtolower(trim($value));
    $value = preg_replace('/[^a-z0-9]+/', '-', $value) ?? '';
    return trim($value, '-') ?: 'section';
}

function extractTitle(string $markdown, string $fallback): string {
    if (preg_match('/^\s*#\s+(.+?)\s*$/m', $markdown, $m)) {
        return trim(strip_tags($m[1]));
    }
    return $fallback;
}

function markdownInline(string $text): string {
    $text = htmlspecialchars($text, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');

    // Inline code first so markdown markers inside code are not interpreted.
    $codes = [];
    $text = preg_replace_callback('/`([^`]+)`/', function ($m) use (&$codes) {
        $key = '___CODE_' . count($codes) . '___';
        $codes[$key] = '<code>' . $m[1] . '</code>';
        return $key;
    }, $text);

    $text = preg_replace_callback('/!\[([^\]]*)\]\(([^)\s]+)(?:\s+"([^"]*)")?\)/', function ($m) {
        $alt = $m[1];
        $url = $m[2];
        $title = isset($m[3]) ? ' title="' . $m[3] . '"' : '';
        if (!preg_match('#^(https?:|mailto:|/|\./|\.\./)#i', $url)) $url = '#';
        return '<img src="' . $url . '" alt="' . $alt . '"' . $title . ' loading="lazy">';
    }, $text);

    $text = preg_replace_callback('/\[([^\]]+)\]\(([^)\s]+)(?:\s+"([^"]*)")?\)/', function ($m) {
        $label = $m[1];
        $url = $m[2];
        $title = isset($m[3]) ? ' title="' . $m[3] . '"' : '';
        if (!preg_match('#^(https?:|mailto:|/|\./|\.\./|#)#i', $url)) $url = '#';
        $external = preg_match('#^https?://#i', $url);
        $attrs = $external ? ' target="_blank" rel="noopener noreferrer"' : '';
        return '<a href="' . $url . '"' . $attrs . $title . '>' . $label . '</a>';
    }, $text);

    $text = preg_replace('/\*\*([^*]+)\*\*/', '<strong>$1</strong>', $text);
    $text = preg_replace('/__([^_]+)__/', '<strong>$1</strong>', $text);
    $text = preg_replace('/(?<!\*)\*([^*]+)\*(?!\*)/', '<em>$1</em>', $text);
    $text = preg_replace('/(?<!\w)_([^_]+)_(?!\w)/', '<em>$1</em>', $text);
    $text = preg_replace('/~~([^~]+)~~/', '<del>$1</del>', $text);

    foreach ($codes as $key => $value) $text = str_replace($key, $value, $text);
    return $text;
}

function renderMarkdown(string $markdown, array &$headings): string {
    $markdown = str_replace(["\r\n", "\r"], "\n", $markdown);
    $lines = explode("\n", $markdown);
    $out = [];
    $paragraph = [];
    $inFence = false;
    $fenceLang = '';
    $code = [];
    $listType = null;
    $blockquote = [];
    $tableHeader = null;
    $tableRows = [];

    $flushParagraph = function() use (&$out, &$paragraph) {
        if (!$paragraph) return;
        $text = trim(implode("\n", $paragraph));
        if ($text !== '') {
            $text = preg_replace('/\n+/', ' ', $text);
            $out[] = '<p>' . markdownInline($text) . '</p>';
        }
        $paragraph = [];
    };

    $flushList = function() use (&$out, &$listType) {
        if ($listType !== null) {
            $out[] = '</' . $listType . '>';
            $listType = null;
        }
    };

    $flushQuote = function() use (&$out, &$blockquote) {
        if (!$blockquote) return;
        $text = implode("\n", $blockquote);
        $blockquote = [];
        $out[] = '<blockquote>' . renderMarkdown($text, $dummy = []) . '</blockquote>';
    };

    $flushTable = function() use (&$out, &$tableHeader, &$tableRows) {
        if ($tableHeader === null) return;
        $html = '<div class="table-wrap"><table><thead><tr>';
        foreach ($tableHeader as $cell) $html .= '<th>' . markdownInline(trim($cell)) . '</th>';
        $html .= '</tr></thead><tbody>';
        foreach ($tableRows as $row) {
            $html .= '<tr>';
            foreach ($row as $cell) $html .= '<td>' . markdownInline(trim($cell)) . '</td>';
            $html .= '</tr>';
        }
        $html .= '</tbody></table></div>';
        $out[] = $html;
        $tableHeader = null;
        $tableRows = [];
    };

    $isTableSeparator = function(string $line): bool {
        $line = trim($line, " |");
        $cells = array_map('trim', explode('|', $line));
        if (!$cells) return false;
        foreach ($cells as $cell) {
            if (!preg_match('/^:?-{3,}:?$/', $cell)) return false;
        }
        return true;
    };

    foreach ($lines as $line) {
        if ($inFence) {
            if (preg_match('/^\s*```/', $line)) {
                $escaped = htmlspecialchars(implode("\n", $code), ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
                $class = $fenceLang !== '' ? ' class="language-' . safeSlug($fenceLang) . '"' : '';
                $out[] = '<div class="code-wrap"><pre><code' . $class . '>' . $escaped . '</code></pre><button class="copy-code" type="button">Copy</button></div>';
                $inFence = false; $fenceLang = ''; $code = [];
            } else {
                $code[] = $line;
            }
            continue;
        }

        if (preg_match('/^\s*```([A-Za-z0-9_+-]*)\s*$/', $line, $m)) {
            $flushParagraph(); $flushList(); $flushQuote(); $flushTable();
            $inFence = true; $fenceLang = $m[1] ?? ''; $code = [];
            continue;
        }

        if ($tableHeader !== null) {
            if ($isTableSeparator($line)) continue;
            if (trim($line) !== '' && str_contains($line, '|')) {
                $cells = array_map('trim', explode('|', trim($line, " |")));
                $tableRows[] = $cells;
                continue;
            }
            $flushTable();
        }

        if ($paragraph && count($paragraph) === 1 && str_contains($paragraph[0], '|') && $isTableSeparator($line)) {
            $tableHeader = array_map('trim', explode('|', trim($paragraph[0], " |")));
            $paragraph = [];
            continue;
        }

        if (preg_match('/^\s{0,3}(#{1,6})\s+(.+?)\s*#*\s*$/', $line, $m)) {
            $flushParagraph(); $flushList(); $flushQuote(); $flushTable();
            $level = strlen($m[1]);
            $raw = trim($m[2]);
            $plain = trim(strip_tags($raw));
            $id = safeSlug($plain);
            $base = $id; $n = 2;
            while (isset($headings[$id])) $id = $base . '-' . $n++;
            $headings[$id] = ['title' => $plain, 'level' => $level];
            $out[] = '<h' . $level . ' id="' . $id . '">' . markdownInline($raw) . '</h' . $level . '>';
            continue;
        }

        if (preg_match('/^\s*(?:---+|\*\*\*+|___+)\s*$/', $line)) {
            $flushParagraph(); $flushList(); $flushQuote(); $flushTable();
            $out[] = '<hr>';
            continue;
        }

        if (preg_match('/^\s*>\s?(.*)$/', $line, $m)) {
            $flushParagraph(); $flushList(); $flushTable();
            $blockquote[] = $m[1];
            continue;
        } elseif ($blockquote) {
            $flushQuote();
        }

        if (preg_match('/^\s*[-*+]\s+(.*)$/', $line, $m)) {
            $flushParagraph(); $flushQuote(); $flushTable();
            if ($listType !== 'ul') { $flushList(); $out[] = '<ul>'; $listType = 'ul'; }
            $out[] = '<li>' . markdownInline($m[1]) . '</li>';
            continue;
        }

        if (preg_match('/^\s*\d+[.)]\s+(.*)$/', $line, $m)) {
            $flushParagraph(); $flushQuote(); $flushTable();
            if ($listType !== 'ol') { $flushList(); $out[] = '<ol>'; $listType = 'ol'; }
            $out[] = '<li>' . markdownInline($m[1]) . '</li>';
            continue;
        }

        if (trim($line) === '') {
            $flushParagraph(); $flushList(); $flushQuote(); $flushTable();
            continue;
        }

        $flushList();
        $paragraph[] = $line;
    }

    if ($inFence) {
        $escaped = htmlspecialchars(implode("\n", $code), ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
        $out[] = '<div class="code-wrap"><pre><code>' . $escaped . '</code></pre><button class="copy-code" type="button">Copy</button></div>';
    }
    $flushParagraph(); $flushList(); $flushQuote(); $flushTable();
    return implode("\n", $out);
}

$files = glob($docsDir . '/*.md') ?: [];
foreach ($files as $file) {
    $name = basename($file);
    $slug = pathinfo($name, PATHINFO_FILENAME);
    $content = file_get_contents($file);
    if ($content === false) continue;
    $headings = [];
    $title = extractTitle($content, ucwords(str_replace(['-', '_'], ' ', $slug)));
    $docs[$name] = [
        'file' => $name,
        'slug' => $slug,
        'title' => $title,
        'content' => $content,
        'html' => renderMarkdown($content, $headings),
        'headings' => $headings,
        'mtime' => filemtime($file) ?: time(),
        'size' => filesize($file) ?: 0,
    ];
}
uasort($docs, fn($a, $b) => strcasecmp($a['title'], $b['title']));

$selected = $_GET['doc'] ?? '';
if (!isset($docs[$selected])) {
    $selected = isset($docs['README.md']) ? 'README.md' : (array_key_first($docs) ?? '');
}
$current = $docs[$selected] ?? null;

function jsonForSearch(array $docs): string {
    $items = [];
    foreach ($docs as $d) {
        $items[] = [
            'file' => $d['file'],
            'title' => $d['title'],
            'text' => trim(preg_replace('/\s+/', ' ', strip_tags($d['html']))),
        ];
    }
    return json_encode($items, JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT);
}
?>
<!doctype html>
<html lang="en">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width,initial-scale=1">
<meta name="description" content="Public documentation portal">
<title><?= htmlspecialchars($current['title'] ?? 'Documentation') ?> — Documentation</title>
<style>
:root{--bg:#f7f8fa;--panel:#fff;--ink:#172033;--muted:#667085;--line:#e4e7ec;--brand:#111827;--soft:#f2f4f7;--code:#0f172a;--shadow:0 10px 30px rgba(16,24,40,.07)}
*{box-sizing:border-box}html{scroll-behavior:smooth}body{margin:0;background:var(--bg);color:var(--ink);font:15px/1.7 Inter,ui-sans-serif,system-ui,-apple-system,BlinkMacSystemFont,"Segoe UI",sans-serif}
a{color:inherit}.top{position:sticky;top:0;z-index:50;background:rgba(255,255,255,.92);backdrop-filter:blur(14px);border-bottom:1px solid var(--line)}
.nav{height:68px;max-width:1500px;margin:auto;padding:0 24px;display:flex;align-items:center;gap:18px}.brand{font-weight:800;letter-spacing:-.03em;white-space:nowrap}.brand span{color:var(--muted);font-weight:500}
.search{position:relative;max-width:520px;flex:1;margin-left:auto}.search input{width:100%;height:42px;border:1px solid var(--line);border-radius:10px;padding:0 14px 0 40px;background:var(--soft);outline:none}.search input:focus{background:#fff;border-color:#98a2b3;box-shadow:0 0 0 3px rgba(152,162,179,.18)}.search svg{position:absolute;left:13px;top:12px;color:var(--muted)}
.layout{max-width:1500px;margin:auto;display:grid;grid-template-columns:280px minmax(0,1fr) 250px;min-height:calc(100vh - 68px)}
.sidebar,.toc{background:#fff}.sidebar{border-right:1px solid var(--line);padding:24px 16px;position:sticky;top:68px;height:calc(100vh - 68px);overflow:auto}.toc{border-left:1px solid var(--line);padding:28px 20px;position:sticky;top:68px;height:calc(100vh - 68px);overflow:auto}
.side-title,.toc-title{text-transform:uppercase;font-size:11px;letter-spacing:.1em;font-weight:800;color:var(--muted);padding:0 10px 10px}
.doclink{display:block;text-decoration:none;padding:10px;border-radius:8px;margin:2px 0}.doclink:hover{background:var(--soft)}.doclink.active{background:#eef0f3;font-weight:700}.file{display:block;font-size:11px;color:var(--muted);font-weight:500;margin-top:1px}
.main{min-width:0;padding:44px clamp(24px,5vw,76px) 80px}.article{max-width:880px;margin:auto}.eyebrow{font-size:12px;color:var(--muted);font-weight:700;text-transform:uppercase;letter-spacing:.08em;margin-bottom:8px}.article>h1:first-child{margin-top:0}
h1,h2,h3,h4,h5,h6{line-height:1.25;letter-spacing:-.025em;scroll-margin-top:90px}h1{font-size:40px;margin:0 0 28px}h2{font-size:28px;margin:48px 0 16px;border-top:1px solid var(--line);padding-top:32px}h3{font-size:21px;margin:34px 0 12px}h4{font-size:17px;margin:26px 0 8px}p{margin:0 0 17px}ul,ol{padding-left:25px;margin:0 0 20px}li{margin:5px 0}blockquote{margin:24px 0;padding:14px 18px;border-left:4px solid #98a2b3;background:var(--soft);border-radius:0 8px 8px 0}hr{border:0;border-top:1px solid var(--line);margin:32px 0}
code{font-family:"SFMono-Regular",Consolas,"Liberation Mono",monospace;font-size:.9em;background:#eef0f3;padding:2px 6px;border-radius:5px} .code-wrap{position:relative;margin:22px 0;border-radius:12px;overflow:hidden;background:var(--code);box-shadow:var(--shadow)}pre{margin:0;padding:20px;overflow:auto;color:#e5e7eb;font:13px/1.65 "SFMono-Regular",Consolas,"Liberation Mono",monospace}pre code{background:none;padding:0;color:inherit}.copy-code{position:absolute;right:10px;top:10px;border:1px solid rgba(255,255,255,.2);background:rgba(255,255,255,.08);color:#fff;border-radius:7px;padding:6px 10px;cursor:pointer}.copy-code:hover{background:rgba(255,255,255,.16)}
.table-wrap{overflow:auto;margin:22px 0}table{border-collapse:collapse;width:100%;min-width:560px;background:#fff;border:1px solid var(--line);border-radius:8px;overflow:hidden}th,td{padding:11px 13px;text-align:left;border-bottom:1px solid var(--line);vertical-align:top}th{background:var(--soft);font-weight:750}tr:last-child td{border-bottom:0}img{max-width:100%;height:auto;border-radius:10px}
.toc a{display:block;text-decoration:none;color:var(--muted);font-size:13px;padding:5px 0}.toc a:hover{color:var(--ink)}.toc a.l3{padding-left:12px}.toc a.l4{padding-left:24px}.toc a.l5,.toc a.l6{padding-left:36px}
.mobile-menu{display:none;border:1px solid var(--line);background:#fff;border-radius:8px;padding:8px 10px}.mobile-drawer{display:none}
.empty{padding:60px 0;text-align:center;color:var(--muted)}.result{display:none;position:absolute;top:48px;left:0;right:0;background:#fff;border:1px solid var(--line);border-radius:10px;box-shadow:var(--shadow);max-height:360px;overflow:auto;padding:8px}.result a{display:block;text-decoration:none;padding:10px;border-radius:7px}.result a:hover{background:var(--soft)}.result small{display:block;color:var(--muted);font-size:11px}
@media(max-width:1100px){.layout{grid-template-columns:250px minmax(0,1fr)}.toc{display:none}}
@media(max-width:760px){.nav{height:60px;padding:0 14px}.brand span{display:none}.mobile-menu{display:block;order:-1}.search{max-width:none}.layout{display:block}.sidebar{display:none;position:fixed;z-index:60;left:0;top:60px;width:290px;height:calc(100vh - 60px);box-shadow:10px 0 30px rgba(0,0,0,.1)}.sidebar.open{display:block}.main{padding:32px 18px 60px}h1{font-size:32px}h2{font-size:24px}.mobile-drawer{display:none;position:fixed;inset:60px 0 0;background:rgba(15,23,42,.3);z-index:55}.mobile-drawer.open{display:block}}
</style>
</head>
<body>
<header class="top">
<nav class="nav">
<button class="mobile-menu" id="menuBtn" aria-label="Open documentation navigation">☰</button>
<div class="brand">Documentation <span>/ Public Portal</span></div>
<div class="search">
<svg width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="11" cy="11" r="7"/><path d="m20 20-3.5-3.5"/></svg>
<input id="searchInput" autocomplete="off" placeholder="Search documentation…">
<div class="result" id="searchResults"></div>
</div>
</nav>
</header>
<div class="mobile-drawer" id="drawer"></div>
<div class="layout">
<aside class="sidebar" id="sidebar">
<div class="side-title">Documentation</div>
<?php if (!$docs): ?>
<div class="empty">No Markdown documents found.</div>
<?php else: foreach ($docs as $d): ?>
<a class="doclink <?= $d['file'] === $selected ? 'active' : '' ?>" href="?doc=<?= rawurlencode($d['file']) ?>">
<?= htmlspecialchars($d['title']) ?><span class="file"><?= htmlspecialchars($d['file']) ?></span>
</a>
<?php endforeach; endif; ?>
</aside>
<main class="main">
<article class="article">
<?php if ($current): ?>
<div class="eyebrow"><?= htmlspecialchars($current['file']) ?></div>
<?= $current['html'] ?>
<?php else: ?>
<div class="empty"><h1>Documentation</h1><p>Add Markdown files to this folder to publish them here.</p></div>
<?php endif; ?>
</article>
</main>
<?php if ($current && count($current['headings']) > 1): ?>
<aside class="toc">
<div class="toc-title">On this page</div>
<?php foreach ($current['headings'] as $id => $h): if ($h['level'] >= 2): ?>
<a class="l<?= (int)$h['level'] ?>" href="#<?= htmlspecialchars($id) ?>"><?= htmlspecialchars($h['title']) ?></a>
<?php endif; endforeach; ?>
</aside>
<?php endif; ?>
</div>
<script>
const docs = <?= jsonForSearch($docs) ?: '[]' ?>;
const input = document.getElementById('searchInput');
const results = document.getElementById('searchResults');
function esc(s){return s.replace(/[&<>"']/g,m=>({'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#039;'}[m]));}
input?.addEventListener('input',()=>{
 const q=input.value.trim().toLowerCase();
 if(!q){results.style.display='none';results.innerHTML='';return;}
 const found=docs.filter(d=>(d.title+' '+d.text).toLowerCase().includes(q)).slice(0,12);
 results.innerHTML=found.length?found.map(d=>`<a href="?doc=${encodeURIComponent(d.file)}"><b>${esc(d.title)}</b><small>${esc(d.file)}</small></a>`).join(''):'<div style="padding:12px;color:#667085">No matching documentation.</div>';
 results.style.display='block';
});
document.addEventListener('click',e=>{if(!e.target.closest('.search'))results.style.display='none';});
document.querySelectorAll('.copy-code').forEach(btn=>btn.addEventListener('click',async()=>{
 const code=btn.parentElement.querySelector('code')?.innerText||'';
 try{await navigator.clipboard.writeText(code);const old=btn.textContent;btn.textContent='Copied';setTimeout(()=>btn.textContent=old,1200);}catch(_){}
}));
const menu=document.getElementById('menuBtn'), side=document.getElementById('sidebar'), drawer=document.getElementById('drawer');
menu?.addEventListener('click',()=>{side.classList.toggle('open');drawer.classList.toggle('open');});
drawer?.addEventListener('click',()=>{side.classList.remove('open');drawer.classList.remove('open');});
</script>
</body>
</html>
