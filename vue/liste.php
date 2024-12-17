<?php 
/**
* Vue : liste des utilisateurs inscrits
*/
?>

<p><?php echo $entete; ?></p>

<table>
	<thead>
		<tr>
			<th>Nom</th>
		</tr>
	</thead>
	<tbody>	
    <?php foreach ($liste as $file) { ?>
    
        <tr>
        		<td>
				<?php echo "<a href='$file'>$file</a><br>"; ?>
            	</td>
        	</tr>
    
    <?php } ?>

	</tbody>
</table>


<?php if(isset($alerte)) { echo AfficheAlerte($alerte);} ?>

<p>
	<a href="index.php">Retour</a>
</p>