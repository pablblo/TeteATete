<p><?php echo $entete; ?></p>

<div class="table-responsive">
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
</div>

<?php if(isset($alerte)) { echo AfficheAlerte($alerte);} ?>

<p>
    <a href="index.php">Retour</a>
</p>