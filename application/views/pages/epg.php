<?php echo js('jquery-1.9.1.js'); ?>
<?php echo js('jquery.base64.js'); ?>
<?php echo js('jstringutil.js'); ?>
<?php echo js('moment.min.js'); ?>
<?php echo js('twix.min.js'); ?>
<?php echo js('jquery.quickfit.js'); ?>
<?php echo js('perfect-scrollbar.jquery.js'); ?>


<?php echo css('perfect-scrollbar.css') ?>


<style type="text/css">
	body
	{
		margin:0;
		padding:0;
	}

	.template
	{
		display:none;
	}

	.header
	{
		height:200px;
	}

	.content
	{
		width:100%;
		text-align: center;
	}


	/* */
	.epg_options
	{
		text-align: left;
		display: inline-block;
		width: 70%;
		margin-bottom: 10px;
	}



	/* */
	#epg_table
	{
		display:inline-block;
	}

	#epg_table .epg_table_content_time_divider, .epg_table_content_channel
	{
		float:left;
	}

	#epg_table .epg_table_content_clear
	{
		clear:both;
	}



	/* */
	.epg_table_header
	{
		background-color: #1A1816;
		white-space: nowrap;
		float:left;
	}

	.epg_table_header_grid_toggle
	{
		width:70px;
		height:47px;
		display:inline-block;
		line-height:47px;
		border-left:1px solid #35322D;
		border-right:1px solid #35322D;
	}

	.epg_table_header_grid_channel
	{
		width:200px;
		height:47px;
		line-height: 47px;
		display:inline-block;
		border-right:1px solid #35322D;
		color:white;
		text-align:center;
	}

	.epg_table_content
	{
		position: relative;
	}

	.epg_table_content_time_divider_grid
	{
		font-size:12px;
		color:white;
		text-align: center;
		width:72px;
		height:300px;
		border-bottom:1px solid #1A1816;
		background:#35322D;
		padding-top:5px;
	}

	.epg_table_content_channels
	{
		float:left;
		position: absolute;
		left: 72px;
		top: 47px;
		bottom: 0px;
	}

	.epg_table_content_channel
	{
		position:relative;
		height:100%;
		border-right:1px solid #e2e2e2;
	}

	.epg_table_content_channel_grid
	{
		width:100%;
		cursor:pointer;
		position:absolute;
		white-space: nowrap;
		text-overflow:ellipsis;
		overflow:hidden;
	}

	.epg_table_content_channel_grid_collapsed
	{
		background-color:#f9f9f9;
	}

	.epg_table_content_channel_grid_text
	{
		white-space: nowrap;
		text-overflow:ellipsis;
		overflow:hidden;
	}

	.epg_table_content_channel_grid:hover
	{
		background:#D0D0D0;
	}

	.epg_table_content_channel_grid_border_line
	{
		top:0px;
		left: 0px;
		right:0px;
		height:1px;
		border-top:1px solid #e2e2e2;
		position:absolute;
	}


	.epg_drawing_canvas
	{
		display:inline-block;
		position:relative;
		width:70%;
		height:500px;
	}

</style>




<script type="text/javascript">


	var dataset = [

	];


	var epg_list =

		jQuery.parseJSON($.base64.decode('<?php echo $epg_channel_list_encoded_json_string ?>'));

</script>




<script type="text/javascript">

	var mEnableSorting               = false;
	var mDisplayAllChannels          = false;
	var mDetailLevel                 = 0;
	var mAdditRelativeSpace          = 0;
	var mEPGParentContainer          = null;
	var mEPGTableContainer           = null;
	var mEPGTableTemplateContainer   = null;
	var mEPGTableDate                = new Date();
	var mEPGTableAvailaleDates       = {};


	function initEPGTableComponents(renderAfterInit)
	{
		mDetailLevel                 = getDetailLevel(getMostAvailableRenderSpace());
		mEPGTableContainer           = $("#epg_table");
		mEPGTableTemplateContainer   = $("#epg_table_templates");
		mEPGParentContainer          = $(mEPGTableContainer).parent();


		// Render the table
		if(renderAfterInit)
		{
			renderEPGTable();
		}


		$(window).resize(onScreenSizeChanged);


		// Init scroller
		mEPGParentContainer.perfectScrollbar();


		// Init others
		initEPGTableOptionController();
	}

	function initEPGTableOptionController()
	{
		if($("#epg_available_dates").length > 0)
		{
			$.each(mEPGTableAvailaleDates, function( key, value )
			{
				var dateStr = key;

				var isSelected = false;

				if(dateStr == moment(mEPGTableDate).format("YYYY-MM-DD"))
				{
					isSelected = true;
				}

				$("#epg_available_dates").append("<option value=\"" + dateStr + "\" " + (isSelected ? "selected=selected" : "") + ">" + dateStr + "</option>");
			});
		}
	}

	function reinitEPGTableDataset()
	{
		dataset = [];

		return initEPGTableDataset();
	}

	function initEPGTableDataset()
	{
		/**
		 *
		 *   Generating dataset
		 *
		 *   Notes:
		 *
		 *      @dataset[%d].schedules[%d].from
		 *      @dataset[%d].schedules[%d].to
		 *
		 *          is relative seconds for the day.
		 *
		 */
		for(var i = 0; i < epg_list.length; i++)
		{
			var new_channel =
				{
					channel:    epg_list[i].channelId,
					name:       epg_list[i].channelName,
					schedules:  []
				};


			var today_formatted_string =
				moment(mEPGTableDate).format("YYYY-MM-DD");

			var today_begin_moment =
				moment(today_formatted_string, "YYYY-MM-DD");


			for(var j=0; j < epg_list[i].schedule.data.length; j++)
			{
				var playTime     = epg_list[i].schedule.data[j].playTime;
				var endTime      = epg_list[i].schedule.data[j].endTime;


				// Format the playTime in dates string
				var playMoment   = moment(playTime, "YYYY-MM-DD HH:mm:ss");
				var playDate     = playMoment.format("YYYY-MM-DD");
				var endMoment    = moment(endTime, "YYYY-MM-DD HH:mm:ss");
				var endDate      = endMoment.format("YYYY-MM-DD");


				// This data will be rendered
				if(playDate == today_formatted_string)
				{
					var from     = moment(today_begin_moment).twix(playMoment).count('seconds');
					var to       = from + moment(playMoment).twix(endMoment).count('seconds');


					// Check if day ends
					if(to > 86400 )
					{
						to = 86400;
					}


					// Create a object
					var schedule  =
					{
						from: from,
						  to: to,
						name: epg_list[i].schedule.data[j].title,
						text: "",
						related_obj: epg_list[i].schedule.data[j]
					}

					console.log("C" + i + " " + epg_list[i].schedule.data[j].title + ":" + playTime + " => " + endTime + " # (" + from + " : " + to + ")");

					new_channel.schedules.push(schedule);
				}


				// Generate available dates array
				if(mEPGTableAvailaleDates[playDate] == null)
				{
					mEPGTableAvailaleDates[playDate] = 1;
				}
			}

			dataset.push(new_channel);
		}

		if(mEnableSorting)
		{
			dataset.sort(function(a, b){return b.schedules.length - a.schedules.length});
		}
	}

	function getRemainingSpace()
	{
		var remainingWhiteSpace   = mEPGParentContainer.outerWidth()  -
			mDetailLevel * $(mEPGTableTemplateContainer).find(".epg_table_header_grid_channel").outerWidth() -
			$(mEPGTableTemplateContainer).find(".epg_table_header_grid_toggle").outerWidth() - 1;

		return remainingWhiteSpace;
	}

	function applyAdditSpaceFittingParent()
	{
		var remainingWhiteSpace = getRemainingSpace();

		var orgWidth = parseInt( $(mEPGTableTemplateContainer).find(".epg_table_header_grid_channel").width());

		if(remainingWhiteSpace > 0)
		{
			var newWidth = orgWidth + Math.floor((remainingWhiteSpace / mDetailLevel));

			// Resize to fit the parent
			$(mEPGTableContainer).find(".epg_table_header_grid_channel").css("width", newWidth);
			$(mEPGTableContainer).find(".epg_table_content_channel").css("width", newWidth);
		}
		else
		{
			// Resize to fit the parent
			$(mEPGTableContainer).find(".epg_table_header_grid_channel").css("width", orgWidth);
			$(mEPGTableContainer).find(".epg_table_content_channel").css("width", orgWidth);
		}
	}

	function renderEPGTable()
	{
		if(getRemainingSpace() < 0)
		{
			mDetailLevel--;
		}

		console.log("render EPG Table, Detail Level = " + mDetailLevel);

		// 1. Render header
		renderEPGTableHeader();


		// 2. Render time dividers
		renderEPGTableContentTimeDividers();


		// 3. Render content schedule
		renderEPGTableContentSchedules();


		// 4. Fitting parent
		applyAdditSpaceFittingParent();
	}

	function renderEPGTableHeader()
	{
		var headerContainer                 = $(mEPGTableContainer).find(".epg_table_header");
		var headerToggleGridTemplate        = $(mEPGTableTemplateContainer).find(".epg_table_header_grid_tpl_toggle");
		var headerChannelTitleGridTemplate  = $(mEPGTableTemplateContainer).find(".epg_table_header_grid_tpl_channel");

		if(headerContainer.length != 1 || headerToggleGridTemplate.length != 1 || headerChannelTitleGridTemplate.length != 1)
		{
			console.log("Failed to render EPGTableHeader");
			return false;
		}

		//  1. Begin generate
		var htmlCode = headerToggleGridTemplate.html();

		for(var i=0; i < mDetailLevel; i++)
		{
			htmlCode += String.format(headerChannelTitleGridTemplate.html(),  dataset[i].name);
		}

		htmlCode += "<div style='clear:both'></div>";


		//  3. Begin render
		headerContainer.html(htmlCode);

		return true;
	}

	function renderEPGTableContentTimeDividers()
	{
		var timeDividersContainer       = $(mEPGTableContainer).find(".epg_table_content > .epg_table_content_time_divider");
		var timeDividersGridTemplate    = $(mEPGTableTemplateContainer).find(".epg_table_content_time_divider_grid_tpl");

		if(timeDividersContainer.length != 1)
		{
			console.log("Failed to render EPGTableContentTimeDividers");
			return false;
		}

		//  Begin generate
		var htmlCode = "";

		for(var i=0; i < 24; i++)
		{
			htmlCode += String.format(timeDividersGridTemplate.html(), getFormattedTimeText(i) );
		}

		timeDividersContainer.html(htmlCode);

		return true;
	}

	function renderEPGTableContentSchedules()
	{
		var channelsContainer       = mEPGTableContainer.find(".epg_table_content_channels");
		var channelTemplate         = mEPGTableTemplateContainer.find(".epg_table_content_channel_tpl");
		var channelGridTemplate     = mEPGTableTemplateContainer.find(".epg_table_content_channel_grid_tpl");

		if(channelsContainer.length != 1 || channelTemplate.length != 1 || channelGridTemplate.length != 1)
		{
			console.log("Failed to render EPGTableContentSchedules");
			return false;
		}

		var minPerPixel = (parseInt($(".epg_table_content_time_divider").outerHeight())) / 86400

		if(minPerPixel <= 0)
		{
			console.log("Failed to render EPGTableContentSchedules");
			return false;
		}

		//  Begin Render
		var htmlCode = "";

		for(var i=0 ; i < mDetailLevel; i++)
		{
			var lastTotalSeconds = 0;

			var newChannelGridHTML = "";

			for(var j=0; j < dataset[i].schedules.length; j++)
			{
				if(i == 0)
				{
					if(dataset[i].schedules[j].from != 0)
					{
						lastTotalSeconds = dataset[i].schedules[j].from;
					}
				}


				// Evaluate grid properties
				var totalSeconds    =  dataset[i].schedules[j].to - dataset[i].schedules[j].from;

				var durationFrom    =  lastTotalSeconds;

				var relativeHeight  =  totalSeconds * minPerPixel;

				var programName     =  relativeHeight < 21 ? "" : dataset[i].schedules[j].name;

				var isCollapsed     =  relativeHeight < 21 ? "epg_table_content_channel_grid_collapsed" : "";

				newChannelGridHTML += String.format(channelGridTemplate.html(),
						programName,
						dataset[i].schedules[j].from,
						dataset[i].schedules[j].to,
						isCollapsed,
						"\" style=\"top:" + durationFrom * minPerPixel + "px; height:" + relativeHeight + "px\""
				);

				lastTotalSeconds += totalSeconds;
			}

			htmlCode += String.format(channelTemplate.html(), newChannelGridHTML);
		}

		channelsContainer.html(htmlCode);


		// Quick fit the fonts
		// inside the table
		// Not implemented
		$(channelsContainer).find(".epg_table_content_channel_grid_text").quickfit({ max: 40, min: 15, truncate: false });


		return true;
	}

	function reRenderEPGTable(forceRender)
	{
		var newDetailLevel = getDetailLevel(getMostAvailableRenderSpace());

		if(forceRender || mDetailLevel != newDetailLevel)
		{
			mDetailLevel = newDetailLevel;

			renderEPGTable();
		}
		else
		{
			applyAdditSpaceFittingParent();
		}
	}

	function enableShowAllChannels()
	{
		mDisplayAllChannels = true;

		reRenderEPGTable();
	}

	function disableShowAllChannels()
	{
		mDisplayAllChannels = false;

		reRenderEPGTable();
	}

	function setEPGTableShownDate(datestr)
	{
		mEPGTableDate = moment(datestr, "YYYY-MM-DD").toDate();

		reinitEPGTableDataset();

		reRenderEPGTable(true);
	}

	function getDetailLevel(width)
	{
		var result = 0;

		if(mDisplayAllChannels)
		{
			result =  dataset.length;
		}
		else
		{
			var numOfChannalRender = (  width - parseInt($("#epg_table_templates .epg_table_header_grid_toggle").outerWidth())) /
				parseInt($("#epg_table_templates .epg_table_header_grid_channel").outerWidth());

			if (numOfChannalRender > dataset.length)
			{
				numOfChannalRender = dataset.length;
			}
			else
			{
				numOfChannalRender = Math.floor(numOfChannalRender);
			}

			result = numOfChannalRender;
		}

		$("#number_of_shown_channels").html(" (" + result + " / " + epg_list.length + ")");

		return result;
	}

	function getMostAvailableRenderSpace()
	{
		var parent = $("#epg_table").parent();

		var spaceInPx =  parent.innerWidth() - parseInt(parent.css("padding-left")) - parseInt(parent.css("padding-right"));

		if(spaceInPx < 1)
		{
			console.log("Failed to getMostAvailableRenderSpace");
		}

		return spaceInPx;
	}

	function onScreenSizeChanged()
	{
		reRenderEPGTable();
	}


	//  Show default EPG table according to
	//  screen size
	$(document).ready
	(
		function()
		{
			initEPGTableDataset();

			initEPGTableComponents(true);
		}
	);


	//  Helper functions
	//
	function getFormattedTimeText(hour)
	{
		var text = (hour % 12) + ":00";

		if(hour < 9 )
		{
			text = "0" + text;
		}
		else if(hour > 12 && hour < 22)
		{
			text = "0" + text;
		}

		if(hour < 13)
		{
			text = text + " am";
		}
		else
		{
			text = text + " pm";
		}

		return text;
	}

</script>


<div class="header">


</div>



<div class="content">


	<div class="epg_options">

		<span id="number_of_shown_channels">

		</span>

		<input type="checkbox" onchange="$(this).is(':checked') ? enableShowAllChannels() : disableShowAllChannels();" /> 顯示所有頻道

		<div>
			<select id="epg_available_dates" onchange="javascript:setEPGTableShownDate($(this).val())">

			</select>
		</div>

	</div>


	<div class="epg_drawing_canvas">


		<!-- The EPG Table -->
		<div id="epg_table">



			<div class="epg_table_header">


			</div>



			<div class="epg_table_content">

				<div class="epg_table_content_time_divider">

				</div>

				<div class="epg_table_content_channels">

				</div>

				<div class="epg_table_content_clear">

				</div>

			</div>


			<div style="clear:both"></div>


		</div>




		<!-- Templates for EPG Table -->
		<div id="epg_table_templates" class="template">


			<!-- Header -->
			<div class="epg_table_header_grid_tpl_toggle"><div class="epg_table_header_grid_toggle">&nbsp;</div></div>



			<div class="epg_table_header_grid_tpl_channel"><div class="epg_table_header_grid_channel">{0}</div></div>



			<!-- Content -->
			<div class="epg_table_content_time_divider_grid_tpl"><div class="epg_table_content_time_divider_grid">{0}</div></div>


			<div class="epg_table_content_channel_tpl"><div class="epg_table_content_channel">{0}</div></div>


			<div class="epg_table_content_channel_grid_tpl"><div class="epg_table_content_channel_grid {3}" from="{1}" to="{2}" ex_attr="{4}"><div class="epg_table_content_channel_grid_border_line"></div><div class="epg_table_content_channel_grid_text">{0}</div></div></div>


		</div>


		<div style="clear:both"></div>


	</div>

</div>