<div class="content">
<table class="table table-striped" id="ordenes">
	<thead>
		<tr>
			<th>ID</th>
			<th>Order</th>
			<th>Names</th>
			<th>Surnames</th>
			<th>DNI</th>
			<th>Amount</th>
			<th>Way to pay</th>
			<th>Reference</th>
			<th>File</th>
		</tr>
	</thead>
	<tbody>
<?php
foreach ($result as $key => $value) {
?>
<tr>
	<td><?php echo $value->ID; ?></td>
	<td><?php echo $value->order; ?></td>
	<td><?php echo $value->names; ?></td>
	<td><?php echo $value->surnames; ?></td>
	<td><?php echo $value->dni; ?></td>
	<td><?php echo $value->amount; ?></td>
	<td>
		<?php echo $value->pay == 1 ? 'Bank deposit' : 'Transfer'; ?>
	</td>
	<td><?php echo $value->reference; ?></td>
	<td><a href="<?php echo $value->fileupload; ?>" target="_blank">Ver</a></td>
</tr>
<?php
}
?>
	</tbody>
</table>
</div>