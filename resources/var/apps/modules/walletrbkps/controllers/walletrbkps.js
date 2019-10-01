App.config(function($stateProvider, HomepageLayoutProvider) {

    $stateProvider.state('walletrbk-payment', {
        url: BASE_PATH+"/walletrbkps/mobile_walletrbk/find/value_id/:value_id/wallet_id/:wallet_id/wallet_customer_id/:wallet_customer_id/amount/:amount",
        controller: 'WalletRbkPSController',
        templateUrl: "modules/walletrbkps/templates/l1/payment.html"
    });
    $stateProvider.state('walletrbk-payment-result', {
        url: BASE_PATH+"/walletrbkps/mobile_rbk/result/value_id/:value_id/wallet_id/:wallet_id/wallet_customer_id/:wallet_customer_id/status/:status",
        controller: 'WalletRbkPSResultController',
        templateUrl: "modules/walletrbkps/templates/l1/payment.html"
    });	

}).controller('WalletRbkPSController', function ($scope, $state, $stateParams,$timeout, $translate, WalletRbkPSFactory,Loader,$window, Dialog,$ionicHistory) {
    
	console.log("WalletRbkPSController fired!");
	
	$scope.data = {};
	WalletRbkPSFactory.value_id = $stateParams.value_id;
	$scope.value_id = $stateParams.value_id;
	$scope.data.value_id = $stateParams.value_id;
	$scope.data.amount = $stateParams.amount;
	$scope.data.wallet_id = $stateParams.wallet_id;
	$scope.data.wallet_customer_id = $stateParams.wallet_customer_id;
    $scope.operation_succes = 0;
	$scope.is_loading = true;
	$scope.old_style = true;
	
	console.log("WalletRbkPSController:");
	console.log($scope.data);
	Loader.show();
	WalletRbkPSFactory.CreateForm($scope.data).then(function (formData) {
		console.log("WalletRbkPSFactory.CreateForm return:");
		console.log(formData);
		//Loader.hide();
		if (formData.success) {
			
            console.log("loading checkout");
            $scope.is_loading = true;
			$scope.is_loading = false;
			//create link to open in browser
			var url = "https://checkout.rbk.money/v1/checkout.html?invoiceID="+formData.response.invoice.id+"&invoiceAccessToken="+formData.response.invoiceAccessToken.payload+"&description="+formData.response.invoice.product+"&locale=auto";
			$window.open(url, "_system", "location=yes");
			$scope.operation_succes = 1;
			$scope.is_loading = false;
		
			
		} else {
			Loader.hide();
			$scope.is_loading = false;
			Dialog.alert($translate.instant("Error"), formData.error_rbk+"<br>"+formData.error_rbk2,"OK") .then(function () {
				$ionicHistory.nextViewOptions({
					historyRoot: true,
					disableAnimate: false
				});
				$state.go('home');
			});
		}

	}, function (error_data) {
		console.log("WalletRbkPSFactory.CreateForm return ERROR:");
		console.log(error_data);	
		Loader.hide();
		Dialog.alert($translate.instant("Error"), error_data.message,"OK");
		$ionicHistory.nextViewOptions({
			historyRoot: true,
			disableAnimate: false
		});
		$state.go('home');
	});
	
	$scope.closeWindow = function() {
		$ionicHistory.nextViewOptions({
			historyRoot: true,
			disableAnimate: false
		});	
		$state.go('home');	
	
	}	

});