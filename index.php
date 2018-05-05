<?php
$ctx = @ibase_pconnect('localhost:/var/www/database/firebird/test2.fdb', 'sysdba', 'masterkey');

if (isset($_GET['rm']) && isset($_GET['id'])) {
	$pk = (int)$_GET['id'];
	ibase_query($ctx, 'DELETE FROM siswa WHERE pk = ?', $pk);

	header('Location: ?laman=index');
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_GET['save'])) {
	$umur = (int)$_POST['UMUR'];
	$kelas = (int)$_POST['KELAS'];

	if (empty($_POST['PK'])) {
		ibase_query($ctx, 'INSERT INTO siswa(nama, umur, kelas) VALUES(?, ?, ?)', $_POST['NAMA'], $umur, $kelas);
	} else {
		$pk = (int)$_POST['PK'];
		ibase_query($ctx, 'UPDATE siswa SET nama = ?, umur = ?, kelas = ? WHERE pk = ?', $_POST['NAMA'], $umur, $kelas, $pk);
	}

	header('Location: ?laman=index');
}

$laman = isset($_GET['laman']) ? $_GET['laman'] :  'index';
$id = empty($_GET['id']) ? null : intval($_GET['id']);
?>
<style>
	body {zoom: 2;}
	table {
		width: 300px;
	}
	table td, table th {
		background-color: #eee;
		border: 1px solid #111;
		padding: 1px 2px;
	}
	table th {
		background-color: #ddd;
	}
</style>

<nav>
	<a href="?laman=index">index</a>
	<a href="?laman=tambah">tambah</a>
</nav>
<br/>

<?php if ($laman === 'index'): ?>
	<?php
	$kelas = ($id === null) ? null : $id;
	$q = $ctx ? ibase_query($ctx, "SELECT pk,nis,keterangan FROM perkelas(?) ORDER BY kelas, kelas", $kelas) : null;
	?>

	<div>
		<select onchange="location.href = '?laman=index&id=' + this.value;" autofocus>
			<option value> -- Pilih Kelas -- </option>
			<?php for($i = 1; $i <= 12; $i++): ?>
				<option value="<?php echo $i ?>" <?php echo ($i === $id) ? 'selected' : null ?>>
					Kelas <?php echo ($i < 10 ? 0 : null), $i ?>
				</option>
			<?php endfor ?>
		</select>
	</div>

	<table>
		<thead>
			<tr>
				<th>NIS</th>
				<th>KETERANGAN</th>
				<th>Aksi</th>
			</tr>
		</thead>

		<tbody>
			<?php $e = true; while($q && $data = ibase_fetch_assoc($q)): $e = false; ?>
				<tr>
					<td><?php echo $data['NIS'] ?></td>
					<td><?php echo $data['KETERANGAN'] ?></td>
					<td>
						<a href="?rm&id=<?php echo $data['PK'] ?>">hapus</a>
						<a href="?laman=ubah&id=<?php echo $data['PK'] ?>">ubah</a>
					</td>
				</tr>
			<?php endwhile ?>
		</tbody>

		<?php if ($e): ?>
			<tbody>
				<tr>
					<td colspan="3" style="text-align: center;">Data Kosong</td>
				</tr>
			</tbody>
		<?php endif ?>
	</table>
<?php endif ?>

<?php if ($laman === 'tambah' || $laman === 'ubah'): ?>
	<?php
	$data = false;
	if ($laman === 'ubah' && $id !== null) {
		$q = $ctx ? ibase_query($ctx, "SELECT * FROM siswa WHERE pk = ?", $id) : null;
		$data = $q ? ibase_fetch_assoc($q) : false; // empty = false
	}

	if ($data === false) $data = new stdClass;
	?>

	<form action="?save" method="post" autocomplete="off">
		<input type="hidden" name="PK"/>
		<div><input name="NAMA" placeholder="NAMA"/></div>
		<div><input name="UMUR" type="number" min="0" placeholder="UMUR"/></div>
		<div><input name="KELAS" type="number" min="1" max="12" placeholder="KELAS"/></div>
		<div><button type="submit">simpan</button></div>
	</form>

	<script>
		(function(fe) {
			var data = <?php echo json_encode($data) ?>;
			for(var d in data) {
				var e = fe.namedItem(d);
				if (e) e.value = data[d];
			}
		})(document.forms[0].elements);
	</script>
<?php endif ?>
