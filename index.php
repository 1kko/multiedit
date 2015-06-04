<?php
include_once("common/lib.php");
?>

<!DOCTYPE html>

<meta charset = "utf-8">
<head>
	<title>MultiEdit - ikko</title>
	<?php include ('common/headers.php'); ?>
</head>

<body>
	<?php include ('common/nav.php'); ?>
	<div class="float-div"><button class="btn btn-xs btn-primary float-button" id="btn_toggle_tree"><small>&lt;</small></button></div>
	
	<div class="container-fluid">
			<div class="col-xs-2 show" id="left_pane">
				<div class="row">
					<div class="col-xs-12">
						<h5>Document Category</h5>
						<div id="div_tree">
						</div>
					</div>
				</div>
				<div class="row">
					<div class="col-xs-12">
						<div class="btn-group" role="group" aria-label="controls">
							<button type="button" id="btn_add_tree_node" class="btn btn-info"><i class="axi axi-add"></i></i></button>
							<button type="button" id="btn_mod_tree_node" class="btn btn-warning"><i class="axi axi-mode-edit"></i></button>
							<button type="button" id="btn_del_tree_node" class="btn btn-danger"><i class="axi axi-minus"></i></i></button>
						</div>
					</div>
				</div>
			</div>

			<div class="col-xs-10" id="editor_pane">
				<div class="col-xs-10">
					<h4 id="h3_category_title"></h3>
				</div>
				<div class="pull-right col-xs-2" id="div_sync_scroll">
					<input type="checkbox" name="Sync" id="sync_Scroll" data-size="mini" data-on-text="ScrollSync" data-off-text="ScrollSync">
				</div>
				<div class="col-xs-12">
					<ol class="breadcrumb" id="ol_breadcrumb"></ol>
				</div>
			</div>
		
		<footer>
			<?php include('common/footer.php'); ?>
		</footer>
</body>
<script type="text/javascript">
	$(document).ready(function() {

	//************ DEFAULTS ************//
		// TinyMCE Editor Configuration with onload complete: call loadContents().
		var tinymceDefaultConf={
			selector: "textarea",
			menubar: false,
			statusbar: false,
			plugins: [
				"advlist autolink lists link print preview anchor",
				"searchreplace visualblocks code fullscreen",
				"insertdatetime media table contextmenu paste jbimages"
			],
			toolbar: "insertfile undo redo | styleselect | bold underline strikethrough |\
					  alignleft aligncenter alignright | bullist numlist outdent indent |\
					  link jbimages | fullscreen",
			toolbar_items_size : 'small',
			relative_urls: false,
			paste_data_images: true,
			setup: function(ed) {
				ed.on('init',function(args) {
					// When project changed, update tinymce editor content.

					// console.log("editor is done");
					project_id=args.target.id.replace("textarea_editor_","");
					category_id=$('#div_tree').jstree('get_selected')[0];
					loadContents(project_id=project_id, category_id=category_id);
				});
			}
		};

		// Notification Widget default config.
		$.notifyDefaults({
			type: 'danger',
			placement: {
				from:  "top",
				align: "right"
			},
			offset: {
				x:10,
				y:40
			}
		});

		var GLOBAL_SCROLL_DATA = {};

	//************ FUNCTIONS ************//
		// Dynamic resize columns for bootstrap
		function resizeCols() {
			$.each(['xs', 'sm', 'md', 'lg'], function(idx, gridSize) {
				$('.col-' + gridSize + '-auto:first').parent().each(function() {
					//we count the number of childrens with class col-md-6
					var numberOfCols = $(this).children('.col-' + gridSize + '-auto').length;
					if (numberOfCols > 0 && numberOfCols < 13) {
						minSpan = Math.floor(12 / numberOfCols);
						remainder = (12 % numberOfCols);
						$(this).children('.col-' + gridSize + '-auto').each(function(idx, col) {
							var width = minSpan;
							if (remainder > 0) {
								width += 1;
								remainder--;
							}
							$(this).removeClass (function (index, css) {
								return (css.match (/(^|\s)col-\S+/g) || []).join(' ');
							});
							$(this).addClass('col-' + gridSize + '-auto');
							$(this).addClass('col-' + gridSize + '-' + width);
						});
					}
				});
			});
		};

		// Dynamic resize tinyMCE Editor
		function resizeEditor(margin, myHeight) {
			margin=typeof margin !== 'undefined' ? margin : 278;
			myEditors = tinymce.get();
			for (var i in myEditors) {
				myEditor=myEditors[i];
				if (myEditor) {
					try {
						if (!myHeight) {
							var targetHeight = window.innerHeight; // Change this to the height of your wrapper element
							var mce_bars_height = 0;
							$('.mce-toolbar, .mce-statusbar, .mce-menubar').each(function(){
								mce_bars_height += $(this).height();
							});
							// console.log('mce bars height total: '+mce_bars_height);
							myHeight = targetHeight - mce_bars_height - margin;  // the extra 8 is for margin added between the toolbars
						}
						// console.log('resizeEditor: ', myHeight);
						myEditor.theme.resizeTo('100%', myHeight);  // sets the dimensions of the editable area
					}
					catch (err) {
						console.log(err);
					}
				}
			}
		};

		// Dynamic resize Contents Viewer
		function resizeViewer(margin, myHeight) {
			margin=typeof margin !== 'undefined' ? margin : 238;
			myViewers = $('.div_contents_viewer').each(function() {
				if (!myHeight) {
					var targetHeight=window.innerHeight;
					myHeight=targetHeight-margin;
					// console.log('resizeViewer: ', $(this), myHeight);
				}
				$(this).height(myHeight);
			});
		}

		// Check if Item changed state
		function wasDeselected (sel, val) {
			if (!val) {
				return true;			
			}
			return sel && sel.some(function(d) { return val.indexOf(d) == -1; })
		};

		// get breadcrumb
		function uiGetParents(loSelectedNode) {
			try {
				var loData = [];
				var lnLevel = loSelectedNode.node.parents.length;
				var lsSelectedID = loSelectedNode.node.id;
				var loParent = $("#" + lsSelectedID);
				for (var ln = 0; ln <= lnLevel - 1 ; ln++) {
					var loParent = loParent.parent().parent();
					if (loParent.children()[1] != undefined) {
						loData.push(loParent.children()[1].text);
					}
				}

				var orData=loData.reverse();
				// insert Self to end.
				orData.push(loSelectedNode.node.text);
				return orData;
			}
			catch (err) {
				$.notify({message:'Error in uiGetParents'});
			}
		};

		function loadContentsEditor(project_id, category_id, contents_data) {
			// push data to target
			editor=tinymce.get("textarea_editor_"+project_id);
			if ( editor!==null ) {
				// console.log("im not null",editor);
				editor.setContent(content_data);
				// update projectID and categoryId
				$("#textarea_editor_"+project_id).attr("projectId", project_id).attr("categoryId", category_id);
				$("#btn_editor_save_"+project_id).attr("projectId", project_id).attr("categoryId", category_id);
				// resize editor 100% height
				resizeEditor();
			}
		};

		function loadContentsViewer(project_id, category_id, contents_data){
			// push data to target
			$("#contents_viewer_"+project_id).html(contents_data);
			// update projectID and categoryId
			$("#contents_viewer_"+project_id).attr("projectId", project_id).attr("categoryId", category_id);

			// Scroll Sync
			$('#sync_Scroll').on('switchChange.bootstrapSwitch', function(event, state){
				if (state===true) {
					$('.div_contents_viewer:last').off('scroll').on('scroll', function(e) {
						var id = $(this).attr('id');
						var target = e.currentTarget;
						var scrollTop = target.scrollTop;
						var lastScrollTop = $(target).data('lastScrollTop') || scrollTop;
						var move = scrollTop - lastScrollTop;

						$('.div_contents_viewer:not("#' + id + '")').each(function(i, obj) {
							$(obj).scrollTop($(obj).scrollTop() + move);
						});

						$(target).data("lastScrollTop", scrollTop);

					});
				} else {
					// console.log("Disableing ScrollSync to ","#contents_viewer_"+project_id);
					// reset global scroll data
					$('.div_contents_viewer:last').removeData("lastScrollTop").off('scroll');
				}
			});

			// Apply colorbox (Lightbox)
			$('#contents_viewer_'+project_id+' img').colorbox({
				rel: project_id, //function() { return $(this).parent.parent().attr("projectId"); }
				inline: true,
				open: false,
				opacity:0.5,
				transition:"none",
				href: function(){ return this; } // streaming contents
			});

			resizeViewer();
		};

		// Update Editor Content View using project_id, category_id
		function loadContents(project_id, category_id){
			if ((project_id) && (category_id)) {
				// console.log("Hello World, project id=",project_id," category_id=",category_id);

				$.ajax({
					url:"./php/contents/?",
					dataType:"json",
					data: {
						"mode":"get",
						"projectId":project_id,
						"categoryId":category_id
					},
					success: function(server_data) {
						// console.log("server_data",server_data)
						content_data="";
						if (server_data.list.length!=0) {
							content_data=server_data.list[0].text;
						}
						// Set content Data To Editor
						loadContentsEditor(project_id, category_id, content_data);
						// Set content Data To Viewer
						loadContentsViewer(project_id, category_id, content_data);
					},
					error: function(request,status,error) {
						$.notify({
							title: request.status+" "+request.statusText,
							message: "(Load Content Data, project_id="+project_id+" category_id="+category_id+")"
						});
					}
				});

				// Default Load
				$('.contents_editor').hide();
				$('.contents_viewer').show();
				$('.content_save').hide();
			}
		};
	//************ INIT ************//
		// Loading Category Tree
		$.ajax({
			url:"./php/category/?",
			dataType:"json",
			data: {
				"mode":"get"
			},
			success: function(data) {
				var treeData=data.list;

				// Populate Tree
				$('#div_tree').jstree({
					'core': {
						// so that create works
						"check_callback" : true,
						'themes': {
							'name':'proton',
							'responsive':true
						},
						'plugins' : [ "themes", "html_data", "ui", "crrm", "contextmenu" ],
						'data': treeData
					},
					'ready': function() {
						// console.log("tree is ready");
					}
				});
			},
			error: function(request,status,error) {
				$.notify({
					title: request.status+" "+request.statusText,
					message: "(Load tree)"
				});

				var treeData=[
					{ "id" : "1", "parent" : "#", "text" : "Error" },
					{ "id" : "2", "parent" : "1", "text" : "Request" },
					{ "id" : "3", "parent" : "2", "text" : request },
					{ "id" : "4", "parent" : "1", "text" : "Status" },
				];

				// Create Root Node
				$("#div_tree").jstree('create_node', '#', {'id' : '0', 'text' : 'Root'}, 'last');

				// Populate Tree
				$('#div_tree').jstree({
					'core': {
						// so that create works
						"check_callback" : true,
						'themes': {
							'name':'proton',
							'responsive':true
						},
						'plugins' : [ "themes", "html_data", "ui", "crrm", "contextmenu" ],
						'data': treeData
					}
				});
			}
		});

		// Loading Project Selector
		$.ajax({
			url:"./php/project/?",
			dataType:"json",
			data: {
				"mode":"get"
			},
			success: function(data) {
				rxData=data.list;
				var retval=[];
				for (var i in rxData) {
					// console.log(rxData[i]);
					var o = new Option(rxData[i].name, rxData[i].id);
					$(o).html(rxData[i].name);
					$('#project_selector').append(o);
				}

				// Cookie Loading.
				project_selection=$.cookie('project_selection');
				// console.log("project_selection",project_selection);

				if (typeof project_selection !== 'undefined') {
					// console.log("loading project_selection",project_selection);
					$('#project_selector').val(project_selection.split(","));
				}
				$('#project_selector').selectpicker('refresh');
			},
			error: function(request,status,error) {
				$.notify({
					title: request.status+" "+request.statusText,
					message: "(Load Project List)"
				});

				var rxData = [
					{"name":"Error Project", "id":1},
					{"name":request.status, "id":2},
					{"name":status, "id":3},
					{"name":error, "id":4}
				];

				for (var i in rxData) {
					// console.log(rxData[i]);
					var o = new Option(rxData[i].name, rxData[i].id);
					$(o).html(rxData[i].name);
					$('#project_selector').append(o);
				}
				// $('#project_selector').selectpicker();
				$('#project_selector').selectpicker('refresh');
			}
		});

		// Initialize Editmode Switch
		$("#toggle_EditMode").bootstrapSwitch();

		// Initialzie Sync Scroll Switch
		$('#sync_Scroll').bootstrapSwitch();

	//************ EVENTS ************//

		// Edit Enabled Event
		$('#toggle_EditMode').on('switchChange.bootstrapSwitch', function(event, state){


			// console.log("toggle_EditMode",event);
			// console.log("contents_editor class: ",$('.contents_editor'));
			// console.log("contents_viewer class: ",$('.contents_viewer'));
			// console.log($(this).val());

			
			if (state===true) {
				// console.log("Enabling");
				$('.contents_editor').show();
				$('.content_save').show();
				$('.contents_viewer').hide();
				// disable scrollSync button
				$("#div_sync_scroll").hide();
			} else {
				$('.contents_editor').hide();
				$('.content_save').hide();
				$('.contents_viewer').show();
				// enable scrollSync button
				$("#div_sync_scroll").show();

				category_id=$('#div_tree').jstree('get_selected')[0];

				// jstree to reload content.
				s_node=$('#div_tree').jstree('get_selected.node');
				$('#div_tree').jstree("deselect_node",s_node);
				$('#div_tree').jstree("select_node",s_node);


				$('#div_tree').on("select_node.jstree", function (e, data) {
					var projectIdList=$('#project_selector').val();
					var category_id=data.selected[0];

					// console.log("select category_id:",category_id,"data:",data);
					$('#toggle_EditMode').bootstrapSwitch('state',false);

					breadcrumbs=uiGetParents(data);
					
					$('#ol_breadcrumb').html("");
					for (var i in breadcrumbs) {
						$('#ol_breadcrumb').append("<li>"+breadcrumbs[i]+"</li>");
					}

					$('#h3_category_title').text(data.node.text);

					for (var i in projectIdList) {
						loadContents(project_id=projectIdList[i], category_id=category_id);
					}
				});

			}
			// console.log(this);
		});



		// Add Button On Tree.
		$('#btn_add_tree_node').on('click',function(){
			var ref = $('#div_tree').jstree(true),
				sel = ref.get_selected();
			if(!sel.length) { return false; }
			sel = sel[0];
			sel = ref.create_node(sel,{"type":"file"});
			if(sel) {
				ref.edit(sel);
			}
		});

		// Mod Button On Tree.
		$('#btn_mod_tree_node').on('click', function(){
			var ref = $('#div_tree').jstree(true),
				sel = ref.get_selected();
			if(!sel.length) { return false; }
			sel = sel[0];
			ref.edit(sel);
		});

		// Del Button On Tree.
		$('#btn_del_tree_node').on('click', function(){
			var ref = $('#div_tree').jstree(true),
				sel = ref.get_selected();
			if(!sel.length) { return false; }
			ref.delete_node(sel);
		});

		// Select Event on Tree: When tree changed, update tinymce editor content.
		$('#div_tree').on("select_node.jstree", function (e, data) {
			var projectIdList=$('#project_selector').val();
			var category_id=data.selected[0];

			// console.log("select category_id:",category_id,"data:",data);
			$('#toggle_EditMode').bootstrapSwitch('state',false);

			breadcrumbs=uiGetParents(data);
			
			$('#ol_breadcrumb').html("");
			for (var i in breadcrumbs) {
				$('#ol_breadcrumb').append("<li>"+breadcrumbs[i]+"</li>");
			}

			$('#h3_category_title').text(data.node.text);

			for (var i in projectIdList) {
				// console.log("div_tree, loadContents");
				loadContents(project_id=projectIdList[i], category_id=category_id);
			}

			// save to cookie
			category_id=$(this).jstree('get_selected')[0];
			if (category_id!=null){
				// console.log("saving cookie category_selection",category_id);
				$.cookie("category_selection",category_id);
			}
		});

		// Add Event on tree
		$('#div_tree').on("create_node.jstree", function (e, data) {
			$.ajax({
				url:"./php/category/?",
				dataType:"json",
				data: {
					"mode":"add",
					"parentId":data.node.parent,
					"name":data.text
				},
				success:function(server_data){
					// console.log("server_data: ", server_data, "tree_data: ", data);
					// console.log("create tree parent_id:"+parentId)
					// console.log("change from:", data.node.id, " to ", server_data.id);
					$('#div_tree').jstree().set_id(data.node, server_data.id);
				}

			});
		});

		// Mod Event on Tree
		$('#div_tree').on("rename_node.jstree", function (e, data) {
			// console.log("rename category_id:"+data.node.id+" text:"+data.text);
			$.ajax({
				url:"./php/category/?",
				dataType:"json",
				data:{
					"mode":"mod",
					"id":data.node.id,
					"parentId":data.node.parent,
					"name":data.text
				}

			});
		});

		// Del Event on Tree.
		$('#div_tree').on("delete_node.jstree", function (e, data) {
			// console.log(data);
			for (var i in data.node.children) {
				// console.log("delete child category_id:"+data.node.children[i]);
				$.ajax({
					url:"./php/category/?",
					dataType:"json",
					data:{
						"mode":"del",
						"id":data.node.children[i],
					}
				});
			}
			// console.log("delete category_id:"+data.node.id);
			$.ajax({
				url:"./php/category/?",
				dataType:"json",
				data:{
					"mode":"del",
					"id":data.node.id,
				}
			});
		});

		// When Project Selector changes, check selected or deselected, and do appropriate action.
		$('#project_selector').on('change', function(event){

			// console.log(event);
			var message, diff,
			$select=$(event.target),
			curr_sel=$select.val(),
			prev_sel=$('select').data('selected');

			// mark Selected Project
			$select.data('selected', curr_sel);

			if ( wasDeselected(prev_sel,curr_sel) ) {
				message="Deselected";
				var diff = $(prev_sel).not(curr_sel).get();
				// console.log(message, diff);
				for (var i in diff){
					project_id=diff[i];
					$("#div_editor_"+project_id).remove();
				}

			} else {
				message="Selected";
				var diff = $(curr_sel).not(prev_sel).get();
				// console.log(message, diff);
				for (var i in diff){
					project_id=diff[i];
					project_name=$("#project_selector option[value='"+project_id+"']").text();
					category_id=$('#div_tree').jstree().get_selected()[i];
					// console.log(message, "project_id:", project_id, "category_id:",category_id);

					var iDiv='<div class="col-md-auto" id="div_editor_'+project_id+'">\
							<div class="col-md-10">\
								<h4>'+project_name+'</h4>\
							</div>\
							<div class="col-md-2 pull-right">\
								<button name="submit" class="content_save btn btn-primary btn-sm pull-right" id="btn_editor_save_'+project_id+'" projectId="'+project_id+'" categoryId="'+category_id+'">Save</button>\
							</div>\
							<div class="col-md-12 contents_editor">\
								<textarea id="textarea_editor_'+project_id+'" projectId="'+project_id+'" categoryId="'+category_id+'"></textarea>\
							</div>\
							<div class="col-md-12 contents_viewer">\
								<div class="div_contents_viewer" id="contents_viewer_'+project_id+'" projectId="'+project_id+'" categoryId="'+category_id+'">\
								Loading Contents\
							</div>\
						</div>';
					$('#editor_pane').append(iDiv);
				}
			}

			// Default Load
			$('.contents_editor').hide();
			$('.contents_viewer').show();
			$('.content_save').hide();


			// resize bootstrap columns.
			resizeCols();

			// Reinitialize TinyMCE
			tinymce.remove();
			tinymce.init(tinymceDefaultConf);

			// Update Content Data to editor.
			for (var i in diff){
				project_id=diff[i];
				category_id=$('#div_tree').jstree('get_selected')[0];

				// console.log("project_selector, loadContents");
				loadContents(project_id=project_id, category_id=category_id);
				$("#textarea_editor_"+project_id).attr("projectId", project_id).attr("categoryId", category_id);
				$("#contents_viewer_"+project_id).attr("projectId", project_id).attr("categoryId", category_id);
			}

			// Save Event on Button after TextArea
			$('.content_save').off('click').on('click', function(){
				var btn=$(this)
				// $("textarea").find("[data-projectId='"+project_id);
				var project_id=$(this).attr("projectId");
				var category_id=$(this).attr("categoryId");
				var content=tinyMCE.get('textarea_editor_'+project_id).getContent();
				var project_name=$("#project_selector option[value='"+project_id+"'").text();


				// console.log("project:", project_id, "category:", category_id, "content:", content);
				$.ajax({
					type:"POST",
					url:"./php/contents/?mode=add&",
					dataType:"json",
					data:{
						"mode":"add",
						"projectId":project_id,
						"categoryId":category_id,
						"text":content
					},
					success: function(request){
						// console.log("project_id=",project_id,"category_id=",category_id,"contents=",content);

						$.ajax({
							url:"./php/contents/?",
							dataType:"json",
							data: {
								"mode":"get",
								"projectId":project_id,
								"categoryId":category_id
							},
							success: function(server_data) {
								// console.log("project_id="+project_id+"&category_id="+category_id, "reply from server: ",server_data);
								loadContentsViewer(project_id=project_id,category_id=category_id,contents=server_data.list[0].text);
							}
						});
						
						// console.log("request=",request);

						if (request.status==="ok") {
							$.notify({
								title:"Successfully saved. ("+project_name+")",
								message: request.status
							}, {
								type:'success'
							});
						} else {
							$.notify({
								title: "Something went wrong. ("+project_name+")",
								message: request.status
							});
						}

					},
					error: function(request,status,error) {
						$.notify({
							title: request.status+" "+request.statusText,
							message: "(Save data)"
						});
					}
				});
			});

			// save to cookie on change.
			if ($(this).val()!=null){
				// console.log("saving cookie project_selection",$(this).val());
				$.cookie('project_selection',$(this).val());
			}

		});

		// Toggle Left Pane Display and Show
		$('#btn_toggle_tree').on('click',function() {
			if ($('#left_pane').hasClass('show'))
			{
				$('#left_pane').removeClass('show');
				$('#left_pane').addClass('hide');
				$('#left_pane').animate({'left':-$('left_pane').width()});
				$('#editor_pane').removeClass('col-md-10');
				$('#editor_pane').addClass('col-md-12');
				$('#div_footer').hide();
				// $('nav').hide();
				$(this).text(">");
				// resizeEditor(margin=150);
				// resizeViewer(margin=150);
				resizeEditor();
				resizeViewer();
			} else {
				$('#left_pane').removeClass('hide');
				$('#left_pane').addClass('show');
				$('#left_pane').animate({'left':0});
				$('#editor_pane').addClass('col-md-10');
				$('#editor_pane').removeClass('col-md-12');
				$('#div_footer').show();
				// $('nav').show();
				$(this).text("<");
				resizeEditor();
				resizeViewer();
			}
		});

		// When Window Resizes, then resize Editor to fit 100% height
		window.onresize = function() {
			resizeEditor();
			resizeViewer();
		};
		
		// Load from cookie category_selection
		category_selection=$.cookie("category_selection");
		$('#div_tree').on('ready.jstree', function(){
			// console.log("loading cookie category_selection", category_selection);
			if (category_selection!='null') {
				$('#div_tree').jstree("select_node",category_selection);
			}
			$('#project_selector').triggerHandler('change');
		});
	});
	
	$(window).load(function(){
		// resizeImages();
		console.log();
	});
</script>


