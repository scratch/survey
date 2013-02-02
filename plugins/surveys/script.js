var survey_current_question = 1;
var survey_questions_per_page = 1;
var survey_total_questions = 0;
var survey_current_page = 1;

function nextQuestion(e) {
	if(survey_questions_per_page != 0) return nextPage(e); // Multi question per page 
	var answered = false;
	
	jQuery("#question-" + survey_current_question + " .answer").each(function(i) {
		if(this.checked) {
			answered = true;
			return true;
		}
	});
	if(!answered) {
		if(!confirm("You did not select any answer. Are you sure you want to continue?")) {
			e.preventDefault();
			e.stopPropagation();
			return false;
		}
	}
	
	jQuery("#question-" + survey_current_question).hide();
	survey_current_question++;
	jQuery("#question-" + survey_current_question).show();
	
	if(survey_total_questions <= survey_current_question) {
		jQuery("#survey-next-question").hide();
		jQuery("#survey-action-button").show();
	}
}

function nextPage(e) {
	survey_current_page++;
	showNextXQuestions();
}

function showNextXQuestions() {
	jQuery(".survey-question").hide();
	
	var from_question = ((survey_current_page - 1) * survey_questions_per_page) + 1;
	var to_question = survey_current_page * survey_questions_per_page;
	for(var i = from_question; i <= to_question; i++) {
		jQuery("#question-" + i).show();
	}
	
	if(to_question >= survey_total_questions) {
		jQuery("#survey-action-button").show();
		jQuery("#survey-next-question").hide();
	}
}

function surveyInit() {
	survey_total_questions = jQuery(".survey-question").length;
	if(survey_questions_per_page > 1) {
		jQuery("#survey-action-button").hide();
		jQuery("#survey-next-question").show();
		showNextXQuestions();
	
	} else if(survey_questions_per_page == 0) { //Single page mode.
		jQuery(".survey-question").show();
		jQuery("#survey-action-button").show();
		jQuery("#survey-next-question").hide();
	
	} else {
		jQuery("#question-1").show();
		if(survey_total_questions == 1) {
			jQuery("#survey-next-question").hide();
		}
	}
	
	jQuery("input.user-answer").focus(function() { jQuery(this).prev().attr('checked', true); });
	jQuery("#survey-next-question").click(nextQuestion);
}

jQuery(document).ready(surveyInit); 
