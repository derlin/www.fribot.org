<?php
/**
 * The theme footer
 * 
 * @package bootstrap-basic
 */
?>

			</div><!--.site-content-->
			
			<script type="text/javascript">
				// add a global variable with the site url for use in script
				site_url = "<?php echo get_site_url(); ?>";
			</script>

			<!-- load fonts -->
			<link href='http://fonts.googleapis.com/css?family=Francois+One' rel='stylesheet' type='text/css'>
			
			<!-- footer -->
			<footer id="site-footer" role="contentinfo">
				<div id="footer-row" class="row site-footer">
					<div class="col-md-6 footer-left">
						<?php 
						if (!dynamic_sidebar('footer-left')) {
							printf(__('Powered by %s', 'bootstrap-basic'), 'WordPress');
							echo ' | ';
							printf(__('Theme: %s', 'bootstrap-basic'), '<a href="http://okvee.net">Bootstrap Basic</a>');
						} 
						?> 
					</div>
					<div class="col-md-6 footer-right text-right">
						<?php dynamic_sidebar('footer-right'); ?> 
					</div>
				</div>
			</footer>
		</div><!--.container page-container-->
		
		
		<!--wordpress footer-->
		<?php wp_footer(); ?> 
	</body>
</html>