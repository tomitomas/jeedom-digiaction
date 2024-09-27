<?php
if (!isConnect('admin')) {
	throw new Exception('{{401 - Accès non autorisé}}');
}
// Déclaration des variables obligatoires
$plugin = plugin::byId('digiaction');
sendVarToJS('eqType', $plugin->getId());
$eqLogics = eqLogic::byType($plugin->getId());
?>

<div class="row row-overflow">
	<!-- Page d'accueil du plugin -->
	<div class="col-xs-12 eqLogicThumbnailDisplay">
		<div class="row">
			<div class="col-sm-10">

				<legend><i class="fas fa-cog"></i> {{Gestion}}</legend>
				<!-- Boutons de gestion du plugin -->
				<div class="eqLogicThumbnailContainer">
					<div class="cursor eqLogicAction" style="color:var(--main-color);" data-action="add">
						<i class="fas fa-plus-circle"></i>
						<br>
						<span>{{Ajouter}}</span>
					</div>
					<div class="cursor eqLogicAction logoSecondary" data-action="gotoPluginConf">
						<i class="fas fa-wrench" style="color:var(--main-color);"></i>
						<br>
						<span>{{Configuration}}</span>
					</div>
				</div>
			</div>
			<?php
			// uniquement si on est en version 4.4 ou supérieur
			$jeedomVersion  = jeedom::version() ?? '0';
			$displayInfoValue = version_compare($jeedomVersion, '4.4.0', '>=');
			if ($displayInfoValue) {
			?>
				<div class="col-sm-2">
					<legend><i class=" fas fa-comments"></i> {{Community}}</legend>
					<div class="eqLogicThumbnailContainer">
						<div class="cursor eqLogicAction logoSecondary" data-action="createCommunityPost" style="color:var(--main-color);">
							<i class="fas fa-ambulance"></i>
							<br>
							<span style="color:var(--txt-color)">{{Créer un post Community}}</span>
						</div>
					</div>
				</div>
			<?php
			}
			?>
		</div>
		<legend><i class="fas fa-table"></i> {{Mes DigiActions}}</legend>
		<!-- Champ de recherche -->
		<div class="input-group" style="margin:5px;">
			<input class="form-control roundedLeft" placeholder="{{Rechercher}}" id="in_searchEqlogic" />
			<div class="input-group-btn">
				<a id="bt_resetSearch" class="btn roundedRight" style="width:30px"><i class="fas fa-times"></i></a>
			</div>
		</div>
		<!-- Liste des équipements du plugin -->
		<div class="eqLogicThumbnailContainer">
			<?php
			foreach ($eqLogics as $eqLogic) {
				$opacity = ($eqLogic->getIsEnable()) ? '' : 'disableCard';
				echo '<div class="eqLogicDisplayCard cursor ' . $opacity . '" data-eqLogic_id="' . $eqLogic->getId() . '">';
				echo '<img src="' . $plugin->getPathImgIcon() . '"/>';
				echo '<br>';
				echo '<span class="name">' . $eqLogic->getHumanName(true, true) . '</span>';
				echo '</div>';
			}
			?>
		</div>
	</div> <!-- /.eqLogicThumbnailDisplay -->

	<!-- Page de présentation de l'équipement -->
	<div class="col-xs-12 eqLogic" style="display: none;">
		<!-- barre de gestion de l'équipement -->
		<div class="input-group pull-right" style="display:inline-flex;">
			<span class="input-group-btn">
				<!-- Les balises <a></a> sont volontairement fermées à la ligne suivante pour éviter les espaces entre les boutons. Ne pas modifier -->
				<a class="btn btn-sm btn-default eqLogicAction roundedLeft" data-action="configure"><i class="fas fa-cogs"></i><span class="hidden-xs"> {{Configuration avancée}}</span>
				</a><a class="btn btn-sm btn-default eqLogicAction" data-action="copy"><i class="fas fa-copy"></i><span class="hidden-xs"> {{Dupliquer}}</span>
				</a><a class="btn btn-sm btn-success eqLogicAction" data-action="save"><i class="fas fa-check-circle"></i> {{Sauvegarder}}
				</a><a class="btn btn-sm btn-danger eqLogicAction roundedRight" data-action="remove"><i class="fas fa-minus-circle"></i> {{Supprimer}}
				</a>
			</span>
		</div>
		<!-- Onglets -->
		<ul class="nav nav-tabs" role="tablist">
			<li role="presentation"><a href="#" class="eqLogicAction" aria-controls="home" role="tab" data-toggle="tab" data-action="returnToThumbnailDisplay"><i class="fas fa-arrow-circle-left"></i></a></li>
			<li role="presentation" class="active"><a href="#eqlogictab" aria-controls="home" role="tab" data-toggle="tab"><i class="fas fa-tachometer-alt"></i><span class="hidden-xs"> {{Équipement}}</span></a></li>
			<li role="presentation"><a href="#actiontab" aria-controls="home" role="tab" data-toggle="tab"><i class="fas fa-cogs"></i><span class="hidden-xs"> {{Actions}}</span></a></li>
			<li role="presentation"><a href="#usertab" aria-controls="home" role="tab" data-toggle="tab"><i class="fas fa-user"></i><span class="hidden-xs"> {{Utilisateurs}}</span></a></li>
		</ul>
		<div class="tab-content">
			<!-- Onglet de configuration de l'équipement -->
			<div role="tabpanel" class="tab-pane active" id="eqlogictab">
				<!-- Partie gauche de l'onglet "Equipements" -->
				<!-- Paramètres généraux de l'équipement -->
				<form class="form-horizontal">
					<fieldset>
						<div class="col-lg-10">
							<div class="col-lg-12">
								<legend><i class="fas fa-wrench"></i> {{Général}}</legend>
								<div class="form-group">
									<label class="col-sm-3 control-label">{{Nom de l'équipement}}</label>
									<div class="col-sm-7">
										<input type="text" class="eqLogicAttr form-control" data-l1key="id" style="display : none;" />
										<input type="text" class="eqLogicAttr form-control" data-l1key="name" placeholder="{{Nom de l'équipement}}" />
									</div>
								</div>
								<div class="form-group">
									<label class="col-sm-3 control-label">{{Objet parent}}</label>
									<div class="col-sm-7">
										<select id="sel_object" class="eqLogicAttr form-control" data-l1key="object_id">
											<option value="">{{Aucun}}</option>
											<?php
											$options = '';
											foreach ((jeeObject::buildTree(null, false)) as $object) {
												$options .= '<option value="' . $object->getId() . '">' . str_repeat('&nbsp;&nbsp;', $object->getConfiguration('parentNumber')) . $object->getName() . '</option>';
											}
											echo $options;
											?>
										</select>
									</div>
								</div>
								<div class="form-group">
									<label class="col-sm-3 control-label">{{Catégorie}}</label>
									<div class="col-sm-9">
										<?php
										foreach (jeedom::getConfiguration('eqLogic:category') as $key => $value) {
											echo '<label class="checkbox-inline">';
											echo '<input type="checkbox" class="eqLogicAttr" data-l1key="category" data-l2key="' . $key . '" />' . $value['name'];
											echo '</label>';
										}
										?>
									</div>
								</div>
								<div class="form-group">
									<label class="col-sm-3 control-label">{{Options}}</label>
									<div class="col-sm-7">
										<label class="checkbox-inline"><input type="checkbox" class="eqLogicAttr" data-l1key="isEnable" />{{Activer}}</label>
										<label class="checkbox-inline"><input type="checkbox" class="eqLogicAttr" data-l1key="isVisible" />{{Visible}}</label>
									</div>
								</div>
								<br />
							</div>

							<div class="col-lg-12">
								<legend><i class="fa fa-cogs"></i> {{Paramètres}}</legend>
								<div class="form-group">
									<label class="col-sm-3 control-label">{{Chaque mode s'auto-appelle}}</label>
									<div class="col-sm-7">
										<label class="checkbox-inline"><input type="checkbox" class="eqLogicAttr" data-l1key="configuration" data-l2key="autocall" />Autoriser</label>
									</div>
								</div>
								<div class="form-group">
									<label class="col-sm-3 control-label">{{Disposition aléatoire des touches numériques du clavier}}
                                        <sup>
											<i class="fas fa-question-circle floatright" style="color: var(--al-info-color) !important;" title="Permet d'avoir un clavier différent à chaque utilisation.<br/>Les touches de 0 à 9 seront aléatoirement melangées pour plus de sécurité"></i>
										</sup>
</label>
									<div class="col-sm-7">
										<label class="checkbox-inline"><input type="checkbox" class="eqLogicAttr" data-l1key="configuration" data-l2key="randomkeys" />Autoriser</label>
									</div>
								</div>
							</div>

							<div class="col-lg-12">
								<legend><i class="fas fa-spell-check"></i> {{Textes}}</legend>
								<div class="form-group">
									<label class="col-sm-3 control-label">{{Temps d'affichage des messages}}</label>
									<div class="col-sm-7">
										<input type="text" class="eqLogicAttr form-control" data-l1key="configuration" data-l2key="countdown" placeholder="{{(en secondes) -1 illimité / 10sec par défaut}}" />
									</div>
								</div>
								<div class="form-group">
									<label class="col-sm-3 control-label">{{Changement de mode réalisé}}
										<sup>
											<i class="fas fa-question-circle floatright" style="color: var(--al-info-color) !important;" title="#eqId# => numéro de l’équipement DigiAction<br/>#eqName# => nom de l’équipement DigiAction<br/>#modeName# => nom du mode qui tente d’être activé<br/>#nbWrongPwd# => nombre de mauvais code saisi"></i>
										</sup>
									</label>
									<div class="col-sm-7">
										<input type="text" class="eqLogicAttr form-control" data-l1key="configuration" data-l2key="textOK" placeholder="{{Actions réalisées pour #modeName#}}" />
									</div>
								</div>
								<div class="form-group">
									<label class="col-sm-3 control-label">{{Mauvais code saisi}}</label>
									<div class="col-sm-7">
										<input type="text" class="eqLogicAttr form-control" data-l1key="configuration" data-l2key="textCodeKO" placeholder="{{Code inconnu}}" />
									</div>
								</div>
							</div>

							<div class="col-lg-12">
								<legend><i class="fas fa-paint-brush"></i> {{Couleurs}}</legend>
								<div class="form-group">
									<label class="col-sm-3 control-label"></label>
									<div class="col-sm-2">
										<label class="text-center" style="width: 48%; display: inline-block;"> {{Arrière-plan}}</label>
										<label class="text-center" style="width: 48%; display: inline-block;"> {{Texte}}</label>
									</div>
								</div>

								<div class="form-group customColor">
									<label class="col-sm-3 control-label">
										{{Par défaut}}
										<sup>
											<i class="fas fa-question-circle floatright" style="color: var(--al-info-color) !important;" title="Couleur que prendra un mode si aucun icône n'est défini"></i>
										</sup>
									</label>
									<div class="col-sm-2">
										<input type="color" class="eqLogicAttr form-control input-sm cursor" data-l1key="configuration" data-type="background-color" data-l2key="colorBgDefault" style="width: 48%; display: inline-block;">
										<input type="color" class="eqLogicAttr form-control input-sm cursor" data-l1key="configuration" data-type="color" data-l2key="colorTextDefault" style="width: 48%; display: inline-block;">
									</div>
									<div class="col-sm-2 ">
										<ul class="digiaction">
											<li class="digiActionExample">Exemple</li>
											<a class="btReinitColor" style="padding-left:10px" title="Reinitialer avec les couleurs par défaut"><i class="fas fa-eraser"></i></a>
										</ul>
									</div>
								</div>
								<div class="form-group autoCallActif customColor" style="display:none;">
									<label class="col-sm-3 control-label">{{Si le mode est actif}}
										<sup>
											<i class="fas fa-question-circle floatright" style="color: var(--al-info-color) !important;" title="Couleur que prendra le mode actif"></i>
										</sup>
									</label>
									<div class="col-sm-2">
										<input type="color" class="eqLogicAttr form-control input-sm cursor" data-l1key="configuration" data-type="background-color" data-l2key="colorBgActif" style="width: 48%; display: inline-block;">
										<input type="color" class="eqLogicAttr form-control input-sm cursor" data-l1key="configuration" data-type="color" data-l2key="colorTextActif" style="width: 48%; display: inline-block;">
									</div>
									<div class="col-sm-2 ">
										<ul class="digiaction">
											<li class="digiActionExample">Exemple</li>
											<a class="btReinitColor" style="padding-left:10px" title="Reinitialer avec les couleurs par défaut"><i class="fas fa-eraser"></i></a>
										</ul>
									</div>
								</div>

							</div>

						</div>

						<!-- Partie droite de l'onglet "Équipement" -->
						<!-- Affiche l'icône du plugin par défaut mais vous pouvez y afficher les informations de votre choix -->
						<div class="col-lg-2">
							<legend><i class="fas fa-info"></i> {{Informations}}</legend>
							<div class="form-group">
								<div class="text-center">
									<img name="icon_visu" src="<?= $plugin->getPathImgIcon(); ?>" style="max-width:160px;" />
								</div>
							</div>
						</div>
					</fieldset>
				</form>
				<hr>
			</div><!-- /.tabpanel #eqlogictab-->

			<!-- Onglet des Actions de l'équipement -->
			<div role="tabpanel" class="tab-pane" id="actiontab">
				<a class="btn btn-success pull-right" id="bt_addMode" style="margin-top: 5px;"><i class="fas fa-plus-circle"></i> {{Ajouter mode}}</a><br /><br />
				<div id="div_modes" class="panel-group"></div>
			</div><!-- /.tabpanel #actiontab-->

			<!-- Onglet des Utilisateurs de l'équipement -->
			<div role="tabpanel" class="tab-pane" id="usertab">
				<a class="btn btn-success pull-right" id="bt_addUser" style="margin-top: 5px;"><i class="fas fa-plus-circle"></i> {{Ajouter un code utilisateur}}</a><br /><br />
				<br /><br />
				<div class="table-responsive">
					<table id="table_user" class="table table-bordered table-condensed">
						<thead>
							<tr>
								<th>{{Actif}}</th>
								<th>{{Panic}}
									<sup>
										<i class="fas fa-question-circle floatright" style="color: var(--al-info-color) !important;" title="Spécifie l'utilisateur/code à utiliser pour activer les opérations correspondant aux actions 'panic'"></i>
									</sup>
								</th>
								<th>{{Nom}}</th>
								<th>{{Code}}
									<sup>
										<i class="fas fa-question-circle floatright" style="color: var(--al-info-color) !important;" title="Uniquement A ou B ou chiffres de 0 à 9"></i>
									</sup>
								</th>
								<th>{{Début}}
									<sup>
										<i class="fas fa-question-circle floatright" style="color: var(--al-info-color) !important;" title="Si non renseigné, actif immédiatement"></i>
									</sup>
								</th>
								<th>{{Durée}}<sup>
										<i class="fas fa-question-circle floatright" style="color: var(--al-info-color) !important;" title="(en minutes)<br/>si vide, alors infini"></i>
									</sup></th>
								<th>{{Début de validité}}</th>
								<th>{{Fin de validité}}</th>
							</tr>
						</thead>
						<tbody>
						</tbody>
					</table>
				</div>
			</div><!-- /.tabpanel #actiontab-->

			<!-- Onglet des commandes de l'équipement -->
			<div role="tabpanel" class="tab-pane" id="usertab2">
				<a class="btn btn-default btn-sm pull-right cmdAction" data-action="add" style="margin-top:5px;"><i class="fas fa-plus-circle"></i> {{Ajouter un code utilisateur}}</a>
				<br /><br />
				<div class="table-responsive">
					<table id="table_cmd" class="table table-bordered table-condensed">
						<thead>
							<tr>
								<th>{{Nom}}</th>
								<th>{{Code}}</th>
							</tr>
						</thead>
						<tbody>
						</tbody>
					</table>
				</div>
			</div><!-- /.tabpanel #usertab-->

		</div><!-- /.tab-content -->
	</div><!-- /.eqLogic -->
</div><!-- /.row row-overflow -->

<!-- Inclusion du fichier javascript du plugin (dossier, nom_du_fichier, extension_du_fichier, id_du_plugin) -->
<?php include_file('desktop', 'digiaction', 'js', 'digiaction');
include_file('desktop', 'digiaction', 'css', 'digiaction');
include_file('3rdparty', 'datetimepicker/jquery.datetimepicker', 'css', 'digiaction');
include_file('3rdparty', 'datetimepicker/jquery.datetimepicker', 'js', 'digiaction'); ?>
<!-- Inclusion du fichier javascript du core - NE PAS MODIFIER NI SUPPRIMER -->
<?php include_file('core', 'plugin.template', 'js'); ?>
