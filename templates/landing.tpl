<!doctype html>
<html lang="ru">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>TestDB — недвижимость</title>
    <style>
        :root { color-scheme: light; }
        * { box-sizing: border-box; }
        body {
            margin: 0;
            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif;
            background: #f4f6f8;
            color: #1a1a1a;
            line-height: 1.5;
        }
        .wrap { max-width: 1100px; margin: 0 auto; padding: 24px 16px 48px; }
        h1 { margin: 0 0 8px; font-size: 2rem; }
        .subtitle { color: #555; margin-bottom: 32px; }
        section { margin-bottom: 40px; }
        h2 { font-size: 1.35rem; margin: 0 0 16px; border-bottom: 2px solid #2563eb; padding-bottom: 6px; }
        .cards { display: grid; grid-template-columns: repeat(auto-fill, minmax(260px, 1fr)); gap: 16px; }
        .card {
            background: #fff;
            border-radius: 10px;
            padding: 16px;
            box-shadow: 0 1px 4px rgba(0,0,0,.08);
        }
        .card h3 { margin: 0 0 8px; font-size: 1.05rem; }
        .meta { font-size: .9rem; color: #444; }
        .meta span { display: block; }
        .price { font-weight: 700; color: #2563eb; margin-top: 8px; }
        .description { margin-top: 10px; font-size: .95rem; }
        .description p { margin: 0 0 .5em; }
        .admin-link { margin-top: 24px; font-size: .9rem; }
        .admin-link a { color: #2563eb; }
        .empty { color: #666; font-style: italic; }
    </style>
</head>
<body>
<div class="wrap">
    <h1>TestDB</h1>
    <p class="subtitle">Каталог домов и квартир на вторичном рынке</p>

    <section>
        <h2>Типы домов</h2>
        {if $houses|@count > 0}
        <div class="cards">
            {foreach from=$houses item=house}
            <article class="card">
                <h3>{$house.NL_HOUSES_SHORT|escape}</h3>
                <div class="meta">
                    {if $house.NL_MATERIAL_SHORT}
                    <span>Материал: {$house.NL_MATERIAL_SHORT|escape}</span>
                    {/if}
                </div>
            </article>
            {/foreach}
        </div>
        {else}
        <p class="empty">Нет данных о домах.</p>
        {/if}
    </section>

    <section>
        <h2>Квартиры</h2>
        {if $apartments|@count > 0}
        <div class="cards">
            {foreach from=$apartments item=apt}
            <article class="card">
                <h3>{$apt.NL_PROP_RESALE_ADDRESS|escape}</h3>
                <div class="meta">
                    <span>Площадь: {$apt.NL_PROP_RESALE_AREA_FULL|escape} м²</span>
                    <span>Этаж: {$apt.NL_PROP_RESALE_FLOOR|escape}</span>
                    {if $apt.NL_VIEW_SHORT}<span>Вид: {$apt.NL_VIEW_SHORT|escape}</span>{/if}
                    {if $apt.NL_HOUSES_SHORT}<span>Тип дома: {$apt.NL_HOUSES_SHORT|escape}</span>{/if}
                    {if $apt.NL_MATERIAL_SHORT}<span>Материал: {$apt.NL_MATERIAL_SHORT|escape}</span>{/if}
                </div>
                {if $apt.NL_PROP_RESALE_COST_TOTAL}
                <div class="price">{$apt.NL_PROP_RESALE_COST_TOTAL|string_format:"%d"} ₽</div>
                {/if}
                {if $apt.description_html}
                <div class="description">{$apt.description_html nofilter}</div>
                {/if}
            </article>
            {/foreach}
        </div>
        {else}
        <p class="empty">Нет объявлений.</p>
        {/if}
    </section>

    <p class="admin-link"><a href="/admin/">Административная панель</a></p>
</div>
</body>
</html>
