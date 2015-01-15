$(function() {

	function nextId() {
		var count = $('#questions .question').length;
		return 'new_' + (count + 1);
	}

	function toggleOptions($question) {
		var val = $question.find('select[name*=type]').val();
		var isSimple = ['PlainText', 'MultilineText', 'FileUpload', 'Email', 'Tel', 'Url', 'Number', 'Date'].indexOf(val) != -1;
		$question.find('.options').toggleClass('hidden', isSimple);
	}

	var questionSort = new Garnish.DragSort($('#questions .question'), {
		caboose: '<div/>',
		handle: '> .actions > .move',
		axis: 'y',
		helperOpacity: 0.9
	});

	$('#questions .question').each(function() {
		toggleOptions($(this));
	});

	$('#add_question').on('click', function() {
		var id = nextId();

		var html = $('#question_template').html();
		html = html.replace(/__QUESTION_ID__/g, id);
		var $question = $(html);

		$question.appendTo('#questions');
		questionSort.addItems($question);

		toggleOptions($question);

		new Craft.EditableTable('questions-' + id + '-options', 'questions[' + id + '][options]', {
			label: {
				heading: 'Label',
				type: 'singleline',
				width: '100%'
			},
			default: {
				heading: 'Default',
				type: 'checkbox'
			}
		});
	});

	$('#questions').on('click', '.actions .delete', function(e) {
		//if (confirm('Really delete this question?')) {
			$(e.currentTarget).parents('.question').remove();
		//}
	});

	$('#questions').on('change', 'select[name*=type]', function(e) {
		var $question = $(e.currentTarget).parents('.question');
		toggleOptions($question);
	});

	$('.elements').on('click', '.delete', function(e) {
		var $row = $(e.currentTarget).parents('tr');
		var id = $row.attr('data-id');
		if (confirm('Are you sure you want to delete this submission?')) {
			Craft.postActionRequest('formerly/submissions/deleteSubmission', { submissionId: id }, function(response) {
				if (response && response.ok) {
					$row.remove();
					Craft.cp.displayNotice('Submission deleted.');
				} else {
					Craft.cp.displayError('Error deleting submission.');
				}
			});
		}
	});

});
