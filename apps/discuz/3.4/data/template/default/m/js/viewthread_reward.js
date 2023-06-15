var specialThread = {
	init: function (json, data) {
		data.rewardprice = json.Variables.special_reward.rewardprice;
		data.bestpost = json.Variables.special_reward.bestpost;
		return data;
	}
};