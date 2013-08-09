<?php
// ===================================================================================================
//                           _  __     _ _
//                          | |/ /__ _| | |_ _  _ _ _ __ _
//                          | ' </ _` | |  _| || | '_/ _` |
//                          |_|\_\__,_|_|\__|\_,_|_| \__,_|
//
// This file is part of the Kaltura Collaborative Media Suite which allows users
// to do with audio, video, and animation what Wiki platfroms allow them to do with
// text.
//
// Copyright (C) 2006-2011  Kaltura Inc.
//
// This program is free software: you can redistribute it and/or modify
// it under the terms of the GNU Affero General Public License as
// published by the Free Software Foundation, either version 3 of the
// License, or (at your option) any later version.
//
// This program is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU Affero General Public License for more details.
//
// You should have received a copy of the GNU Affero General Public License
// along with this program.  If not, see <http://www.gnu.org/licenses/>.
//
// @ignore
// ===================================================================================================

class Kaltura_Client_PartnerAggregation_Enum_DwhHourlyPartnerOrderBy
{
	const AGGREGATED_TIME_ASC = "+aggregatedTime";
	const AGGREGATED_TIME_DESC = "-aggregatedTime";
	const SUM_TIME_VIEWED_ASC = "+sumTimeViewed";
	const SUM_TIME_VIEWED_DESC = "-sumTimeViewed";
	const AVERAGE_TIME_VIEWED_ASC = "+averageTimeViewed";
	const AVERAGE_TIME_VIEWED_DESC = "-averageTimeViewed";
	const COUNT_PLAYS_ASC = "+countPlays";
	const COUNT_PLAYS_DESC = "-countPlays";
	const COUNT_LOADS_ASC = "+countLoads";
	const COUNT_LOADS_DESC = "-countLoads";
	const COUNT_PLAYS25_ASC = "+countPlays25";
	const COUNT_PLAYS25_DESC = "-countPlays25";
	const COUNT_PLAYS50_ASC = "+countPlays50";
	const COUNT_PLAYS50_DESC = "-countPlays50";
	const COUNT_PLAYS75_ASC = "+countPlays75";
	const COUNT_PLAYS75_DESC = "-countPlays75";
	const COUNT_PLAYS100_ASC = "+countPlays100";
	const COUNT_PLAYS100_DESC = "-countPlays100";
	const COUNT_EDIT_ASC = "+countEdit";
	const COUNT_EDIT_DESC = "-countEdit";
	const COUNT_SHARES_ASC = "+countShares";
	const COUNT_SHARES_DESC = "-countShares";
	const COUNT_DOWNLOAD_ASC = "+countDownload";
	const COUNT_DOWNLOAD_DESC = "-countDownload";
	const COUNT_REPORT_ABUSE_ASC = "+countReportAbuse";
	const COUNT_REPORT_ABUSE_DESC = "-countReportAbuse";
	const COUNT_MEDIA_ENTRIES_ASC = "+countMediaEntries";
	const COUNT_MEDIA_ENTRIES_DESC = "-countMediaEntries";
	const COUNT_VIDEO_ENTRIES_ASC = "+countVideoEntries";
	const COUNT_VIDEO_ENTRIES_DESC = "-countVideoEntries";
	const COUNT_IMAGE_ENTRIES_ASC = "+countImageEntries";
	const COUNT_IMAGE_ENTRIES_DESC = "-countImageEntries";
	const COUNT_AUDIO_ENTRIES_ASC = "+countAudioEntries";
	const COUNT_AUDIO_ENTRIES_DESC = "-countAudioEntries";
	const COUNT_MIX_ENTRIES_ASC = "+countMixEntries";
	const COUNT_MIX_ENTRIES_DESC = "-countMixEntries";
	const COUNT_PLAYLISTS_ASC = "+countPlaylists";
	const COUNT_PLAYLISTS_DESC = "-countPlaylists";
	const COUNT_BANDWIDTH_ASC = "+countBandwidth";
	const COUNT_BANDWIDTH_DESC = "-countBandwidth";
	const COUNT_STORAGE_ASC = "+countStorage";
	const COUNT_STORAGE_DESC = "-countStorage";
	const COUNT_USERS_ASC = "+countUsers";
	const COUNT_USERS_DESC = "-countUsers";
	const COUNT_WIDGETS_ASC = "+countWidgets";
	const COUNT_WIDGETS_DESC = "-countWidgets";
	const AGGREGATED_STORAGE_ASC = "+aggregatedStorage";
	const AGGREGATED_STORAGE_DESC = "-aggregatedStorage";
	const AGGREGATED_BANDWIDTH_ASC = "+aggregatedBandwidth";
	const AGGREGATED_BANDWIDTH_DESC = "-aggregatedBandwidth";
	const COUNT_BUFFER_START_ASC = "+countBufferStart";
	const COUNT_BUFFER_START_DESC = "-countBufferStart";
	const COUNT_BUFFER_END_ASC = "+countBufferEnd";
	const COUNT_BUFFER_END_DESC = "-countBufferEnd";
	const COUNT_OPEN_FULL_SCREEN_ASC = "+countOpenFullScreen";
	const COUNT_OPEN_FULL_SCREEN_DESC = "-countOpenFullScreen";
	const COUNT_CLOSE_FULL_SCREEN_ASC = "+countCloseFullScreen";
	const COUNT_CLOSE_FULL_SCREEN_DESC = "-countCloseFullScreen";
	const COUNT_REPLAY_ASC = "+countReplay";
	const COUNT_REPLAY_DESC = "-countReplay";
	const COUNT_SEEK_ASC = "+countSeek";
	const COUNT_SEEK_DESC = "-countSeek";
	const COUNT_OPEN_UPLOAD_ASC = "+countOpenUpload";
	const COUNT_OPEN_UPLOAD_DESC = "-countOpenUpload";
	const COUNT_SAVE_PUBLISH_ASC = "+countSavePublish";
	const COUNT_SAVE_PUBLISH_DESC = "-countSavePublish";
	const COUNT_CLOSE_EDITOR_ASC = "+countCloseEditor";
	const COUNT_CLOSE_EDITOR_DESC = "-countCloseEditor";
	const COUNT_PRE_BUMPER_PLAYED_ASC = "+countPreBumperPlayed";
	const COUNT_PRE_BUMPER_PLAYED_DESC = "-countPreBumperPlayed";
	const COUNT_POST_BUMPER_PLAYED_ASC = "+countPostBumperPlayed";
	const COUNT_POST_BUMPER_PLAYED_DESC = "-countPostBumperPlayed";
	const COUNT_BUMPER_CLICKED_ASC = "+countBumperClicked";
	const COUNT_BUMPER_CLICKED_DESC = "-countBumperClicked";
	const COUNT_PREROLL_STARTED_ASC = "+countPrerollStarted";
	const COUNT_PREROLL_STARTED_DESC = "-countPrerollStarted";
	const COUNT_MIDROLL_STARTED_ASC = "+countMidrollStarted";
	const COUNT_MIDROLL_STARTED_DESC = "-countMidrollStarted";
	const COUNT_POSTROLL_STARTED_ASC = "+countPostrollStarted";
	const COUNT_POSTROLL_STARTED_DESC = "-countPostrollStarted";
	const COUNT_OVERLAY_STARTED_ASC = "+countOverlayStarted";
	const COUNT_OVERLAY_STARTED_DESC = "-countOverlayStarted";
	const COUNT_PREROLL_CLICKED_ASC = "+countPrerollClicked";
	const COUNT_PREROLL_CLICKED_DESC = "-countPrerollClicked";
	const COUNT_MIDROLL_CLICKED_ASC = "+countMidrollClicked";
	const COUNT_MIDROLL_CLICKED_DESC = "-countMidrollClicked";
	const COUNT_POSTROLL_CLICKED_ASC = "+countPostrollClicked";
	const COUNT_POSTROLL_CLICKED_DESC = "-countPostrollClicked";
	const COUNT_OVERLAY_CLICKED_ASC = "+countOverlayClicked";
	const COUNT_OVERLAY_CLICKED_DESC = "-countOverlayClicked";
	const COUNT_PREROLL25_ASC = "+countPreroll25";
	const COUNT_PREROLL25_DESC = "-countPreroll25";
	const COUNT_PREROLL50_ASC = "+countPreroll50";
	const COUNT_PREROLL50_DESC = "-countPreroll50";
	const COUNT_PREROLL75_ASC = "+countPreroll75";
	const COUNT_PREROLL75_DESC = "-countPreroll75";
	const COUNT_MIDROLL25_ASC = "+countMidroll25";
	const COUNT_MIDROLL25_DESC = "-countMidroll25";
	const COUNT_MIDROLL50_ASC = "+countMidroll50";
	const COUNT_MIDROLL50_DESC = "-countMidroll50";
	const COUNT_MIDROLL75_ASC = "+countMidroll75";
	const COUNT_MIDROLL75_DESC = "-countMidroll75";
	const COUNT_POSTROLL25_ASC = "+countPostroll25";
	const COUNT_POSTROLL25_DESC = "-countPostroll25";
	const COUNT_POSTROLL50_ASC = "+countPostroll50";
	const COUNT_POSTROLL50_DESC = "-countPostroll50";
	const COUNT_POSTROLL75_ASC = "+countPostroll75";
	const COUNT_POSTROLL75_DESC = "-countPostroll75";
	const COUNT_LIVE_STREAMING_BANDWIDTH_ASC = "+countLiveStreamingBandwidth";
	const COUNT_LIVE_STREAMING_BANDWIDTH_DESC = "-countLiveStreamingBandwidth";
	const AGGREGATED_LIVE_STREAMING_BANDWIDTH_ASC = "+aggregatedLiveStreamingBandwidth";
	const AGGREGATED_LIVE_STREAMING_BANDWIDTH_DESC = "-aggregatedLiveStreamingBandwidth";
}

