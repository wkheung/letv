<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class EPG extends LETV_Controller {
	
	function __construct()
	{
		parent::__construct();
		$this->output->unset_template();
	}
	
	private function getChannelsListJSONString()
	{
		$json = @file_get_contents('http://api.live.letv.com/v1/channel/letv/101/1010');

		if($json != "")
		{
			$channelList = json_decode($json, true)["rows"];

			// Using the returned channel list
			// to obtains its channel schedule
			$channelsSchedule = @$this->getChannelsSchedule($channelList);

			if($channelsSchedule != "")
			{
				// Merge channel list and
				// its schedule
				for($i = 0; $i < count($channelList); $i++)
				{
					$currentChannelObj = &$channelList[$i];

					$isScheduleFound = false;

					for($j = 0; $j < count($channelsSchedule); $j++)
					{
						if($currentChannelObj["channelId"] == $channelsSchedule[$j]["channelId"])
						{
							$currentChannelObj["schedule"] = $channelsSchedule[$j];
							$isScheduleFound  = true;
							break;
						}
					}

					if(!$isScheduleFound )
					{
						throw new Exception("API: getChannelsList (" . $currentChannelObj["channelId"] . ") failed.");
					}
				}

				return base64_encode(json_encode($channelList, true));
			}
			else
			{
				throw new Exception("API: getChannelsList failed.");
			}
		}
		else
		{
			throw new Exception("API: getChannelsList failed.");
		}
	}

	private function getChannelsString($channelList, $from, $to)
	{
		$delimter = "";

		$channelsString  = "";

		for($i = $from; $i < $to; $i++)
		{
			$channelsString .= $delimter . $channelList[$i]["channelId"];
			$delimter = ",";
		}

		return $channelsString;
	}

	private function getChannelsScdeduleByGroup($channelIds)
	{
		$json = @file_get_contents('http://api.live.letv.com/v1/playbill/current/1001?channelIds=' . $channelIds);

		if($json != "")
		{
			return @json_decode($json, true)["rows"];
		}
		else
		{
			throw new Exception("API: getChannelsSchedule failed (" . $channelIds .  ").");
		}
	}

	private function getChannelsSchedule($channelList)
	{
		$schedule_data  = array();

		$num_of_channel = count($channelList);

		$num_of_remaining_channel = $num_of_channel;

		$start_index = 0;

		do
		{
			if($num_of_remaining_channel > 10)
			{
				$taken_channel_counter = 10;
			}
			else
			{
				$taken_channel_counter = $num_of_remaining_channel;
			}

			$num_of_remaining_channel -= $taken_channel_counter;

			$fragment = @$this->getChannelsScdeduleByGroup(@$this->getChannelsString($channelList, $start_index, $start_index + $taken_channel_counter));

			if($fragment != "")
			{
				$schedule_data = array_merge($schedule_data, $fragment);
			}

			$start_index += $taken_channel_counter;
		}
		while($num_of_remaining_channel > 0);

		if(count($schedule_data) == $num_of_channel)
		{
			return $schedule_data;
		}
		else
		{
			throw new Exception("API: getChannelsSchedule failed.");
		}
	}

	public function index()
	{
		try
		{
			$data = array("epg_channel_list_encoded_json_string" => $this->getChannelsListJSONString());

			$this->load->view('pages/epg', $data);
		}
		catch(Exception $ex)
		{
			echo $ex->getMessage();
		}
	}
}
