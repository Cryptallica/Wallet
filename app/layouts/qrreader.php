<div id="cryptQrKeys">
	<div id="cryptQRKeys" class="cryptallicaKeys">
		<a href="javascript:cryptallica.toggler.setPage('home');cryptallicaQR.stopScanner();">Home</a>
		
		<span id="cryptQRKeyData">
			<a href="javascript:;" data="camera">
				<img src="assets/use/camera.png" alt="camera icon">
			</a>
			
			<a href="javascript:;" data="import">
				<img src="assets/use/image.png" alt="image icon">
			</a>
		</span>
	</div>
</div>

<div id="cryptQrContent">
	<div data="camera">
		<div id="cryptCameraWrap">
			<span id="cryptCameraWindow"></span>
			<a id="cryptScanner">Scanning</a>
		</div>		
	</div>

	<div data="import">
		<div id="cryptImportDiv">
			<canvas id="cryptQRFile"></canvas>
			<span>
				<h2>Drag Files Over</h2>
				<input type="file" id="cryptImportUpload" onchange="cryptallicaQR.handleFiles(this.files);">
			</span>
		</div>
	</div>
	
	<div id="cryptScannerMessage"></div>
</div>