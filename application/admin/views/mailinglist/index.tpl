{extends file="standard.tpl"}

{block name=body}
<h2>{$lang_create} {$lang_subscriber}</h2>

<p>
	<a href="{$root}admin/mailinglist/create">{$lang_create} {$lang_subscriber|lower}</a>
</p>
{/block}