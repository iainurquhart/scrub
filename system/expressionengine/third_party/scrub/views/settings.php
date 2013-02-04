

<script type='text/javascript'>
	
	// Return a helper with preserved width of cells
	var fixHelper = function(e, ui) {
	    ui.children().each(function() {
	        $(this).width($(this).width());
	    });
	    return ui;
	};
	
	$(document).ready(function() {
		
		var $container = $(".roland_table tbody").roland();
		var opts = $.extend({}, $.fn.roland.defaults);
		
		$(".roland_table tbody").sortable({
			helper: fixHelper, // fix widths
			handle: '.roland_drag_handle',
			cursor: 'move',
			update: function(event, ui) { 
				$.fn.roland.updateIndexes($container, opts); 
			}
		});

	});
</script>

<p>Scrub applies filters to submitted content on save, meaning you can choose what html elements and attributes are allowed and disallowed. This add-on will not filter fields which do not store their output as text. For example, Matrix &amp; Playa fields are NOT filterable, but the native text input, textarea, RTE editor for example are filterable</p>

<p>Use with caution and thoroughly test your filters before deploying in a production environment.</p>

<?php echo form_open('C=addons_extensions'.AMP.'M=save_extension_settings'.AMP.'file=scrub');?>

<?php

	// echo "<pre>"; print_r($settings); echo "</pre>";
		
	$this->table->set_template($roland_template);
	
	$this->table->set_heading(
			array('data' => '', 'style' => 'width: 10px;'),
			array('data' => lang('field'), 'style' => ''),
			array('data' => lang('filters'), 'style' => ''),
			array('data' => '', 'style' => 'width: 47px;')
		);
	
	if($settings)
	{
		$i = 0;
		foreach($settings['fields'] as $settings)
		{

			$settings['filter']['safe'] = (isset($settings['filter']['safe'])) ? $settings['filter']['safe'] : '';
			$settings['filter']['tidy'] = (isset($settings['filter']['tidy'])) ? $settings['filter']['tidy'] : '';
			$settings['filter']['remove_comments'] = (isset($settings['filter']['remove_comments'])) ? $settings['filter']['remove_comments'] : '';
			$settings['filter']['remove_empty_paras'] = (isset($settings['filter']['remove_empty_paras'])) ? $settings['filter']['remove_empty_paras'] : '';
			$settings['filter']['deny_attributes'] = (isset($settings['filter']['deny_attributes'])) ? $settings['filter']['deny_attributes'] : '';
			$settings['filter']['elements'] = (isset($settings['filter']['elements'])) ? $settings['filter']['elements'] : '';

			$this->table->add_row(
				array('data' => $drag_handle, 'class' => 'roland_drag_handle'),
				form_dropdown("fields[$i][field_id]", $field_groups, $settings['field_id']),
				"<span style='line-height: 20px;'>".
				" Tidy HTML:<br />".form_dropdown("fields[$i][filter][tidy]", $tidy_options, $settings['filter']['tidy'])."<br /><br />".
				form_checkbox("fields[$i][filter][safe]", '1', $settings['filter']['safe'])." Safe HTML only<br />".
				form_checkbox("fields[$i][filter][remove_comments]", '1', $settings['filter']['remove_comments'])." Remove HTML Comments<br />".
				form_checkbox("fields[$i][filter][remove_empty_paras]", '1', $settings['filter']['remove_empty_paras'])." Remove Empty Paragraphs<br /><br />".
				"Deny Attributes: (comma seperated eg <strong>class, style</strong>) ".form_input("fields[$i][filter][deny_attributes]", $settings['filter']['deny_attributes'])." <br />".
				"Elements: (comma seperated eg &nbsp; <strong>* -div, -blink</strong> &nbsp; would allow everything except divs and blink tags) ".form_input("fields[$i][filter][elements]", $settings['filter']['elements'])." <br />".
				"</span>",
				array('data' => $nav, 'class' => 'roland_nav')
			);
			$i++;		
		}
	}
	else
	{
		$this->table->add_row(
			array('data' => $drag_handle, 'class' => 'roland_drag_handle'),
			form_dropdown("fields[0][field_id]", $field_groups, ''),
			"<span style='line-height: 20px;'>".
			" Tidy HTML:<br />".form_dropdown("fields[0][filter][tidy]", $tidy_options, '')."<br /><br />".
			form_checkbox("fields[0][filter][safe]", '1', '')." Safe HTML only<br />".
			form_checkbox("fields[0][filter][remove_comments]", '1', '')." Remove HTML Comments<br />".
			form_checkbox("fields[0][filter][remove_empty_paras]", '1', '')." Remove Empty Paragraphs<br /><br />".
			"Deny Attributes:  (comma seperated eg <strong>class, style/</strong>)".form_input("fields[0][filter][deny_attributes]", '', '')." <br />".
			"Elements: (comma seperated eg <strong>* -div, -blink</strong>)".form_input("fields[0][filter][elements]", '', '')." <br />".
			"</span>",
			array('data' => $nav, 'class' => 'roland_nav')
		);
	}
	
	echo $this->table->generate();
	echo form_submit('submit', 'Submit', 'class="submit"');
	echo form_close();

?>