<tr class="row-editing" style="%%GLOBAL_ProductRowStyle%%" data-product-id="%%GLOBAL_ProductID%%">
<td colspan="14">
<form id="FormProductEditinline">
<input type="hidden" name="productid" value="%%GLOBAL_ProductID%%" />
<div class="ProductRowInlineLeft">
<table>
%%GLOBAL_ProductInlineFieldsLeft%%
</table>
</div>
<div class="ProductRowInlineRight">
<table>
%%GLOBAL_ProductInlineFieldsRight%%
<tr>
	<td colspan="2">%%LNG_ProductPriceCompetenciaFull%%:</td>
</tr>
<tr>
	<td colspan="2">
		<input type="hidden" id="ProductFieldLastKey" value="%%GLOBAL_ProductFieldLastKey%%" />
		<a href="#" onclick="return AddProductField()"><img src="%%GLOBAL_AppPath%%\images\addicon.png" alt="%%LNG_Add%%" />%%LNG_AddRow%%</a><br />
		<table id="ProductPrecioCompetencia">
		%%GLOBAL_ProductInlineCompareFields%%
		</table>
	</td>
</tr>
</table>
</div>
<div class="ProductRowInlineBottom">
	<a class="js-submit" href="#"><img src="%%GLOBAL_AppPath%%/images/icon-save.png" /></a>
	<a class="js-cancel" href="#"><img src="%%GLOBAL_AppPath%%/images/icon-cancel.png" /></a>
</div>
</form>
</td>
</tr>
<script type="text/javascript">
	$(document).ready(function () {
		$(".ProductRowInlineBottom").on("click", ".js-submit", product_submit);
		$(".ProductRowInlineBottom").on("click", ".js-cancel", product_cancel);
	});
</script>