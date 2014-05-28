<tr id="productFieldTR_%%GLOBAL_ProductFieldKey%%" class="CreateProductPrecioCompetenciaRow">
<td><input type="text" name="ProductFieldPrecioCompetenciaNombre[%%GLOBAL_ProductFieldKey%%]" class="ProductFieldPrecioCompetenciaNombre[%%GLOBAL_ProductFieldKey%%] input inputText50" value="%%GLOBAL_FormFieldPostCreateProductPrecioCompetenciaNombre%%"/></td>
<td><input type="text" name="ProductFieldPrecioCompetenciaPrecio[%%GLOBAL_ProductFieldKey%%]" class="ProductFieldPrecioCompetenciaPrecio[%%GLOBAL_ProductFieldKey%%] input inputText50" value="%%GLOBAL_FormFieldPostCreateProductPrecioCompetenciaPrecio%%"/></td>
<td>
	<select id="ProductFieldPrecioCompetenciaTipo[%%GLOBAL_ProductFieldKey%%]" name="ProductFieldPrecioCompetenciaTipo[%%GLOBAL_ProductFieldKey%%]">
		<option value="UsuarioFinal" %%GLOBAL_ProductFieldUsuarioFinalSelected%%>%%LNG_UsuarioFinal%%</option>
		<option value="Mayoreo" %%GLOBAL_ProductFieldMayoreoSelected%%>%%LNG_Mayoreo%%</option>
		<option value="Online" %%GLOBAL_ProductFieldOnlineSelected%%>%%LNG_Online%%</option>
	</select>  
</td>
<td><a href="#" onclick="return DelProductField(%%GLOBAL_ProductFieldKey%%)"><img src="%%GLOBAL_AppPath%%\images\delicon.png" alt="%%LNG_Delete%%" /></a></td>
</tr>