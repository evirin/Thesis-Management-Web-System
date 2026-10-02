var base_url = document.location.origin
var base_path = '/thesis_management';



$(document).ready(function(){
	// Get the loaded url once the document is ready
	var currentUrl = $(location).attr('href');
	// Get the connected user role. We need it for the Ajax Pool for the notifications
	getUserRole();
	// Load the correct view based on the current url
	redirect(currentUrl);
	// Overwrite the redirect of <a> elements with our custom redirection method by adding the class .link to them
	$(document).on('click', '.link', function(e){
		e.preventDefault();
		var url = $(this).attr('href');
		redirect(url);
	})
	// Universal form submition.
	$(document).on('submit', 'form', function (e) {
		// Prevent default form submission
	    e.preventDefault(); 
		// Cache the form
	    var $form = $(this); 
		// Get form action URL
	    var action = $form.attr('action'); 
		// Get form method (e.g., POST, GET)
	    var method = $form.attr('method'); 
		var requiredFields = $form.find('input[required], textarea[required]');
		var requiredCheck = true;
		//Preload function
		var preloadFunction = $form.attr('data-preload'); 

		requiredFields.each(function(){
			if($(this).val() == ''){
				requiredCheck = false;
			}

		})
		// Check if all required fields are filled
		if(requiredCheck){
			// Check if the form contains file inputs
			var hasFiles = $form.find('input[type="file"]').length > 0;

			// Prepare data based on the form type
			var formData;
			var contentType = 'application/x-www-form-urlencoded'; // Default content type
			var processData = true; 

			if (hasFiles) {
				// If there are file inputs, use FormData
				formData = new FormData($form[0]);
				contentType = false; // Let jQuery set the content type to multipart/form-data
				processData = false; // Prevent jQuery from transforming the FormData object
			} else {
				// Otherwise, serialize form data
				formData = $form.serialize();
			}

			// Send the AJAX request
			$.ajax({
				url: action,
				type: method,
				data: formData,
				contentType: contentType, 
				processData: processData, 
				dataType: 'json',
				beforeSend: function(){
					if(preloadFunction){
						var fn = window[preloadFunction];

						if (typeof fn === "function") { 
							fn();
						}
					}
				},
				success: function (response) {
				
					if (response.redirect_to) {
						// Handle redirection
						var target_url = base_url + base_path + '/' + response.redirect_to;
						redirect(target_url, response.message);
					}
					if (response.callback) {
						
						var fn = window[response.callback]; 
						if (typeof fn === "function") { 
							fn(); 
						}
					}
					setTimeout(function(){
						if($('#main-container').hasClass('loading')){
							$('#main-container').removeClass('loading');
							$('.loader').fadeOut();
						}
					},1000)
				},
				error: function (response) {
					if($('#main-container').hasClass('loading')){
						$('#main-container').removeClass('loading');
						$('.loader').fadeOut();
					}
					console.log(response);
				}
			});
		}
		else{
			var message = {
				type : 'error',
				content : 'Please fill required fields'
			};
			showMessage(message);
		}
	});

	$(document).on('click', '.close-message', function(){
		$('#message').fadeOut()
	})

	$(document).on('click', '.submit-form', function(){
		var thisForm = $(this).attr('data-target');
		$('#'+thisForm).submit();
	})

	$(document).on('change', '#full-description', function(){
		const fileInput = $(this)[0]; 
        const files = fileInput.files;
        const $container = $('#preview-uploaded-files');

        $container.empty();

        if (files.length > 0) {
            for (let i = 0; i < files.length; i++) {
                const fileName = files[i].name; 
                $container.append(`<div class="uploaded-file"><i class="fa-regular fa-file-pdf"></i><span class="uploaded-filename">${fileName}</span></div>`);
            }
        }
	})

	$(document).on('click', '.bottom-panel .select-student span', function(){
		$('.student-list').slideToggle();
		$('#search-students').slideToggle();
	});

	$(document).on('click', '.open-committee', function(){
		$('.invite-fields').slideToggle().css('display', 'flex');
	});

	$(document).on('click', '.change-status', function(){
		var statusId = $(this).attr('data-status');
		var statusName = $(this).text();

		$('input[name="status"]').val(statusId);
		$('.status-extra-field span').text(statusName)

		if ($(this).hasClass('cancel-thesis')){
			$('.cancellation-wrapper').slideDown();
		}
		else{
			$('.cancellation-wrapper').slideUp();
			var status = $(this).attr('data-status');
			var thesis = $(this).attr('data-thesis');
			$.ajax({
				url: base_path+'/api/set-status.php',
				dataType: 'json',
				type: 'POST',
				data: {'thesis' : thesis, 'status' : status},
				success: function(response){
					if (response.redirect_to) {
			            // Handle redirection
			            var target_url = base_url + base_path + '/' + response.redirect_to;
			            redirect(target_url, response.message);
			        }
			        else{
			        	if (response.message.content != undefined){
							showMessage(response.message);
						}
			        }

				},
				error: function(response){
					console.log(response)
				}
			})
		}

	})

	$(document).on('click', '.main-menu .link', function(){
		$('.main-menu .link').removeClass('selected')
		$(this).addClass('selected');
	})

	$(document).on('click', '.status-extra-field', function(){
		$('.change-status-panel').slideToggle();
	})

	$(document).on('click', '.student-list .student', function(){
		$('.student-list').slideUp();
		$('#search-students').slideUp();
		var thisId = $(this).attr('data-user');
		var status = $(this).attr('data-status');
		var statusName = $(this).attr('data-status-name');
		var thisTime = $(this).attr('data-time')
		$('input[name="assigned_on"]').val(thisTime)
		$('input[name="student"]').val(thisId);
		$('input[name="status"]').val(status)
		$('.select-student span').text($(this).text())
		$('.status-extra-field span').text(statusName)
		$('.status-extra-field span').removeAttr('class');
		$('.status-extra-field span').addClass('status-'+status)
	});

	$(document).on('input', '#search-students', function(){
		$('.student-list .student').hide();
		var thisSearch = $(this).val().toLowerCase();

		
		var students = $('.student-list').find('.student');

		students.each(function(){
			var studentName = $(this).find('.student-name').text().toLowerCase();
			var studentNumber = $(this).find('.student-number').text().toLowerCase();

			if (studentName.includes(thisSearch) || studentNumber.includes(thisSearch)){
        		$(this).show()
        	}
        	else{
        		$(this).hide()
        	}
		})
	})

	$(document).on('input', '#search-professors', function(){

		$('.professors-list .professor').hide();
		var thisSearch = $(this).val().toLowerCase();

		var professors = $('.professors-list').find('.professor');

		professors.each(function(){
			var professorName = $(this).find('.professor-name').text().toLowerCase();

			if (professorName.includes(thisSearch)){
        		$(this).show()
        	}
        	else{
        		$(this).hide()
        	}
		})
	})

	
	$(document).on('click', '.member-status-', function(){
		var thesis_id = $(this).attr('data-thesis');
		var professor_id = $(this).attr('data-user');

		cancelInvite(thesis_id, professor_id);
	})

	$(document).on('click', '.send-invite', function(){
		var professor_id = $(this).attr('data-user');
		var thesis_id = $(this).closest('.professors-list').attr('data-thesis');
		var name = $(this).find('.professor-name').text().trim();
		sendInvite(thesis_id, professor_id);
		appendCommittee(professor_id, thesis_id, name);
		$(this).remove();
	})

	$(document).on('click', '.accept-invite', function(){
		var thesis_id = $(this).closest('.invite').attr('data-thesis');
		$(this).closest('.invite').remove();
		acceptInvite(thesis_id);
	})

	$(document).on('click', '.decline-invite', function(){
		var thesis_id = $(this).closest('.invite').attr('data-thesis');
		$(this).closest('.invite').remove();
		declineInvite(thesis_id);
	})

	$(document).on('click', '.add-links', function(){
		$('.links-wrapper').append('<input id="extra-links" type="text" name="links[]" placeholder="Your link here (Youtube, Drive files, etc.)">')
	})

	$(document).on('click', '.filter-status', function(){
		$('.statuses').slideToggle();
	})
	
	$(document).on('click', '.statuses .select-status', function(){
		var thisStatus = $(this).attr('data-status');
		var thisText = $(this).text().trim();
		if (thisStatus.length){
			$('.filter-status .holder').text(thisText);
			$('.v-row').slideUp()
			$('.v-row[data-status="'+thisStatus+'"]').slideDown()
		}
		else{
			$('.v-row').slideDown()
		}
		
	})

	$(document).on('click', '.filter-instructor', function(){
		$('.instructor-options').slideToggle();
	})

	$(document).on('click', '.filter-instructor .option', function(){
		var thisText = $(this).text().trim();
		var thisValue = $(this).attr('data-instructor');

		if (thisValue != 'all'){
			$('.v-row').slideUp();
			$('.v-row[data-instructor="'+thisValue+'"]').slideDown();
		}
		else{
			$('.v-row').slideDown();
		}
	})

	$(document).on('click', '.mobile-toggle', function(){
		$('.left-bar').fadeIn().css('display', 'flex');
	})

	$(document).on('click', '.close-menu', function(){
		$('.left-bar').fadeOut();
	})

	$(document).on('click', '.export-wrapper', function () {
		$('.export-options').slideToggle();
	})

	$(document).on('click', '.notifications', function () {
		$('.notifications').removeClass('unread');
		$('.notifications i').removeClass('fa-solid');
		$('.notifications i').addClass('fa-regular');
		if (!$('.notification-bar').hasClass('opened')){
			$('.notification-bar').addClass('opened');
			$.ajax({
		        url: base_path+'/views/layout/notifications.php',
		        type: 'GET',
		        success: function (response) {
		        	$('.notification-bar .all-invites').html(response);
		        },
		        error: function (xhr, status, error) {
		            console.error('An error occurred:', error);
		        }
	    	});
		}
		else{
			$('.notification-bar').removeClass('opened');	
		}
	})

	$(document).on('click', '.export-json', function () {
	    $.ajax({
	        url: base_path+'/api/gen-thesis-export.php',
	        type: 'GET',
	        success: function (data) {
	        	
	        	const jsonData = JSON.stringify(data, null, 2);
	            // Create a Blob from the JSON string
	            const blob = new Blob([jsonData], { type: 'application/json' });

	            // Create a download link
	            const link = document.createElement('a');
	            link.href = window.URL.createObjectURL(blob);
	            link.download = 'theses.json';
	            document.body.appendChild(link);
	            link.click();
	            document.body.removeChild(link);
	        },
	        error: function (xhr, status, error) {
	            console.error('An error occurred:', error);
	        }
	    });
	});

	$(document).on('click', '.export-csv', function () {
	    $.ajax({
	        url: base_path+'/api/gen-thesis-export.php', // Update with the correct path
	        type: 'GET',
	        success: function (data) {
	            // Convert the data to CSV format
				console.log(data)
	            const csvData = convertToCSV(data);

	            // Create a Blob from the CSV string
	            const blob = new Blob([csvData], { type: 'text/csv' });

	            // Create a download link
	            const link = document.createElement('a');
	            link.href = window.URL.createObjectURL(blob);
	            link.download = 'theses.csv'; // Set the name of the CSV file
	            document.body.appendChild(link);
	            link.click();
	            document.body.removeChild(link);
	        },
	        error: function (xhr, status, error) {
	            console.error('An error occurred:', error);
	        }
	    });
	});

	// Function to convert JSON data to CSV
	function convertToCSV(data) {
		// Extract headers (keys) from the first object in the array
		const headers = [
			'thesis_id',
			'topic',
			'created_by_name',
			'created_by_surname',
			'assigned_to_name',
			'assigned_to_surname',
			'created_at',
			'status',
			'committee_members'
		];
	
		// Create a header row
		const csvRows = [headers.join(',')];
	
		// Add rows for each object
		data.forEach(row => {
			const values = headers.map(header => {
				switch (header) {
					case 'created_by_name':
						return `"${row.created_by?.name || ''}"`;
					case 'created_by_surname':
						return `"${row.created_by?.surname || ''}"`;
					case 'assigned_to_name':
						return `"${row.assigned_to?.name || ''}"`;
					case 'assigned_to_surname':
						return `"${row.assigned_to?.surname || ''}"`;
					case 'status':
						return `"${row.status?.status || ''}"`;
					case 'committee_members':
						// Combine all committee members into a single string
						return `"${row.committee_members.map(member => `${member.name} ${member.surname}`).join('; ') || ''}"`;
					default:
						// For simple fields, add them directly
						return `"${String(row[header] || '').replace(/"/g, '""')}"`;
				}
			});
			csvRows.push(values.join(','));
		});
	
		// Combine all rows with newlines
		return csvRows.join('\n');
	}
	
	// Example usage:
	// const csvData = convertToCSV(data);
	// console.log(csvData);
	



})

//The basic redirection function. 
function redirect(url, message = {}){
	
	if (message.content != undefined){
		showMessage(message);
	}

	getView(url)
	.then(data => {

		// View name
		var view = data.view;
		// View subpages of exist
		var subpages = data.subpages;
		var subpages_uri = '';

		// Build subpages uri of they exist
		if (subpages != null){
			for (var i = 0; i < subpages.length; i++) {
				subpages_uri += '/'+subpages[i];
			}
		}

		// Build the final url
		var newUrl = base_url+base_path+'/'+view+subpages_uri;
		//Replace the final url to the addess bar
		window.history.pushState(null, null, newUrl);
		// Paint the view
		loadView(view, subpages);
	})
}

// Gets the html of the required view and pastes it to the #main-container
function loadView(view = 'home', subpages = null){

	$.ajax({
		url: base_path+'/api/get-view.php',
		dataType: 'html',
		type: 'POST',
		data: {'view' : view, 'subpages' : subpages},
		success: function(response){
			$('body').removeAttr('class');
			$('body').addClass('template-'+view)
			$('#main-container').html(response)

			//If the chart container exists generate the charts
			if ($('#thesisChart').length > 0){
				generateThesisChart();
			}
			
		},
		error: function(response) {
            console.log(response);
        }
	})

}

//Returns view name based on the provided url
function getView(currentUrl){
	// Since we want this async function to return a value we are using promises. 
	return new Promise((resolve, reject) => {
		$.ajax({
			url: base_path+'/api/get-route.php?url='+currentUrl,
			dataType: 'json',
			type: 'GET',
			success: function(response){
				resolve(response)
			},
			error: function(response) {
	            reject(response)
	        }
		})
	})
}

//Generates the error & success messages and displays them
function showMessage(message){
	$('#message .message-counter').css({'width':'0px'});
	var messageType = message.type;
	var messageContent = message.content;

	$('#message').removeAttr('class');
	$('#message').addClass(message.type);
	$('#message p').text(message.content);
	$('#message').fadeIn().css('display', 'flex')
	$('#message .message-counter').animate({
		'width' : '100%'
	}, 5000);
	setTimeout(function(){
		$('#message').fadeOut();
	}, 5000)
}

function sendInvite(thesis_id, professor_id){
	$.ajax({
		url: base_path+'/api/send-invite.php',
		dataType: 'json',
		type: 'POST',
		data: {'thesis_id' : thesis_id, 'professor_id' : professor_id},
		success: function(response){
			if (response.redirect_to) {
	            // Handle redirection
	            var target_url = base_url + base_path + '/' + response.redirect_to;
	            redirect(target_url, response.message);
	        }
	        else{
	        	if (response.message.content != undefined){
	        		
					showMessage(response.message);
				}
	        }

		},
		error: function(response){
			console.log(response)
		}
	})
}

function cancelInvite(thesis_id, professor_id){
	$.ajax({
		url: base_path+'/api/cancel-invite.php',
		dataType: 'json',
		type: 'POST',
		data: {'thesis_id' : thesis_id, 'professor_id' : professor_id},
		success: function(response){

			if (response.redirect_to) {
	            // Handle redirection
	            var target_url = base_url + base_path + '/' + response.redirect_to;
	            redirect(target_url, response.message);
	        }
	        else{
	        	if (response.message.content != undefined){
					showMessage(response.message);
				}
	        }
	        $('.committee-member[data-user="'+professor_id+'"]').remove();
		},
		error: function(response){
			console.log(response)
		}
	})
}

function acceptInvite(thesis_id){
	$.ajax({
		url: base_path+'/api/accept-invite.php',
		dataType: 'json',
		type: 'POST',
		data: {'thesis_id' : thesis_id,},
		success: function(response){
			if (response.redirect_to) {
	            // Handle redirection
	            var target_url = base_url + base_path + '/' + response.redirect_to;
	            redirect(target_url, response.message);
	        }
		},
		error: function(response){
			console.log(response)
		}
	})
}

function declineInvite(thesis_id){
	$.ajax({
		url: base_path+'/api/decline-invite.php',
		dataType: 'json',
		type: 'POST',
		data: {'thesis_id' : thesis_id,},
		success: function(response){
			if (response.redirect_to) {
	            // Handle redirection
	            var target_url = base_url + base_path + '/' + response.redirect_to;
	            redirect(target_url, response.message);
	        }
		},
		error: function(response){
			console.log(response)
		}
	})
}

function closeNotifications(){
	$('.notification-bar').removeClass('opened');
}

function preload(){
	$('#main-container').addClass('loading');
	$('.loader').fadeIn().css('display', 'grid');
}

function generateMenu() {
    $.ajax({
	    url: base_path+'/views/layout/main-menu.php', // Update with the correct path to your PHP file
	    type: 'GET',
	    success: function (response) {
	    	$('.left-bar .main-menu').html(response);
	    },
	    error: function (xhr, status, error) {
	        console.error('An error occurred:', error);
	    }
	});

	$.ajax({
	    url: base_path+'/views/layout/top-menu.php', // Update with the correct path to your PHP file
	    type: 'GET',
	    success: function (response) {
	    	$('.top-bar').html(response);
	    },
	    error: function (xhr, status, error) {
	        console.error('An error occurred:', error);
	    }
	});

	getUserRole();
}

let notificationInterval;
let statusInterval;

function getUserRole(){
	$.ajax({
	    url: base_path+'/api/get-user-role.php', // Update with the correct path to your PHP file
	    type: 'GET',
	    success: function (response) {
	    	if (response == 2){
	    		const pollingInterval = 5000; // Check every 5 seconds (5000 milliseconds)
                // Check if an interval already exists and clear it to prevent duplicates
                if (notificationInterval) {
                    clearInterval(notificationInterval);
                }

				if (statusInterval) {
                    clearInterval(statusInterval);
                }
                // Start a new interval for notifications
                notificationInterval = setInterval(checkForInvitations, pollingInterval);
				// Start a new interval for thesis
                statusInterval = setInterval(checkForStatus, pollingInterval);
	    	}
	    	else{
	    		if (notificationInterval) {
                    clearInterval(notificationInterval);
                    notificationInterval = null; // Reset the interval ID
                }
				if (statusInterval) {
                    clearInterval(statusInterval);
                    statusInterval = null; // Reset the interval ID
                }
	    	}
	    },
	    error: function (xhr, status, error) {
	        console.error('An error occurred:', error);
	    }
	});
}

//function that returns the thesis statuses to check changes
function checkForStatus(){
	
	getView(window.location.href).then(data => {
		if(data.view == 'view-thesis'){
			var thesis_id = data.subpages[0];
			$.ajax({
				url: base_path+'/api/check-thesis-status.php?thesis_id='+thesis_id, // Update with the correct path to your PHP file
				type: 'GET',
				dataType: 'json',
				success: function (response) {
					var oldStatus = $('input[name="old_status"]').val();
					let status = response[0].status;

					if(oldStatus != status.id){
						console.log('Status changed');
						redirect(window.location.href);
					}
				},
				error: function (xhr, status, error) {
					console.log('stop fetching')
					console.error('An error occurred:', error);
					if (statusInterval) {
						clearInterval(statusInterval);
						statusInterval = null; // Reset the interval ID
					}
				}
			});
		}

		if(data.view == 'list-thesis'){
			var statuses = [];
			var old_statuses = [];
			var thesesRows = $('.v-row');
			thesesRows.each(function(){
				old_statuses.push($(this).attr('data-status'));
			})
			
			$.ajax({
				url: base_path+'/api/check-all-theses-status.php', // Update with the correct path to your PHP file
				type: 'GET',
				dataType: 'json',
				success: function (response) {
					if(response.length > 0){
						for (let i = 0; i < response.length; i++) {
							statuses.push(response[i].status.id.toString());
						}

						//If the statuses are different load the view again
						if(areArraysDifferent(old_statuses, statuses)){
							redirect(window.location.href);
						}
					}
				},
				error: function (xhr, status, error) {
					console.log('stop fetching')
					console.error('An error occurred:', error);
					if (statusInterval) {
						clearInterval(statusInterval);
						statusInterval = null; // Reset the interval ID
					}
				}
			});
		}

	})
}

function checkForInvitations() {
	var oldCount = $('.invites-inner-wrapper').attr('data-count');
	$.ajax({
	    url: base_path+'/api/check-invitation.php', // Update with the correct path to your PHP file
	    type: 'GET',
	    success: function (response) {
	    	
	    	var newCount = response.length;
	    	if (newCount > oldCount){
	    		$('.notifications').addClass('unread')
	    		$('.notifications i').removeClass('fa-regular')
	    		$('.notifications i').addClass('fa-solid')
	    	}
	    },
	    error: function (xhr, status, error) {
	    	console.log('stop fetching')
	        if (notificationInterval) {
                clearInterval(notificationInterval);
                notificationInterval = null; // Reset the interval ID
            }
	    }
	});
}

function areArraysDifferent(arr1, arr2) {
	// First, check if their lengths are different
	if (arr1.length !== arr2.length) {
	  return true; // Arrays are different if lengths don't match
	}
  
	// Compare each element in the arrays
	for (let i = 0; i < arr1.length; i++) {
	  if (arr1[i] !== arr2[i]) {
		return true; // Arrays are different if any element is not the same
	  }
	}
  
	return false; // Arrays are identical
  }
  

function appendCommittee(id, thesis, name){
	var html = '<div class="committee-member member-status-" data-user="'+id+'" data-thesis="'+thesis+'"><div class="member-name">'+name+'</div><div class="member-date"><div class="invited-date"></div></div></div>';

	$('.committee-list').append(html);
}

function generateThesisChart(){

	$.ajax({
		url: base_path+'/api/get-all-theses.php',
		dataType: 'json',
		type: 'GET',
		success: function(theses){
			
			var thesis_int_number = theses.as_instructor.length;
			var thesis_com_number = theses.as_committee.length;
			var completed = 0;
			var assigned = 0;
			var cancelled = 0;
			var unassigned = 0;

			var instructorGrades = [];
        	var committeeGrades = [];

			for (var i = 0; i < thesis_int_number; i++) {

				if (theses.as_instructor[i].final_grade){
					instructorGrades.push(theses.as_instructor[i].final_grade);
				}

				if (theses.as_instructor[i].thesis_status_id == 1){
					unassigned++;
				}
				if (theses.as_instructor[i].thesis_status_id == 2 || theses.as_instructor[i].thesis_status_id == 3 || theses.as_instructor[i].thesis_status_id == 4){
					assigned++;
				}
				if (theses.as_instructor[i].thesis_status_id == 6){
					cancelled++;
				}
				if (theses.as_instructor[i].thesis_status_id == 5){
					completed++;
				}
			}
			
			for (var i = 0; i < thesis_com_number; i++) {
				if (theses.as_committee[i].final_grade){
					committeeGrades.push(theses.as_committee[i].final_grade);
				}
			}

			drawThesesChart(theses.length, completed, assigned, cancelled, unassigned)
			drawActivityChart(thesis_int_number, thesis_com_number)

			
			generateGradeChart(instructorGrades, committeeGrades);
		},
		error: function(response){
			console.log(response)
		}
	})

	function drawThesesChart(totalTheses, completed, assigned, cancelled, unassigned) {
	    // Total count
	    const total = completed + assigned + cancelled + unassigned;

	    // Data configuration for the doughnut chart
	    const data = {
	        labels: ['Completed', 'Assigned', 'Cancelled', 'Unassigned'],
	        datasets: [{
	            data: [completed, assigned, cancelled, unassigned],
	            backgroundColor: [
	                'rgba(57, 165, 57, 0.7)', // Completed
	                'rgba(245, 136, 40, 0.7)', // Assigned
	                'rgba(180, 49, 40, 0.7)', // Cancelled
	                'rgba(170, 170, 170, 0.7)', // Unassigned
	            ],
	            borderColor: [
	                'rgba(57, 165, 57, 1)', // Completed
	                'rgba(245, 136, 40, 1)', // Assigned
	                'rgba(180, 49, 40, 1)', // Cancelled
	                'rgba(170, 170, 170, 1)', // Unassigned
	            ],
	            borderWidth: 1
	        }]
	    };

	    // Chart configuration
	    const config = {
	        type: 'doughnut',
	        data: data,
	        options: {
	            responsive: true,
	            plugins: {
	                legend: {
	                    position: 'top',
	                },
	                tooltip: {
	                    callbacks: {
	                        label: function (tooltipItem) {
	                            const value = tooltipItem.parsed;
	                            const percentage = ((value / total) * 100).toFixed(2);
	                            return `${tooltipItem.label}: ${value} (${percentage}%)`;
	                        }
	                    }
	                }
	            }
	        }
	    };

	    // Render the chart
	    const ctx = document.getElementById('thesisChart').getContext('2d');
	    const thesisChart = new Chart(ctx, config);
	}

	function drawActivityChart(thesis_int_number, thesis_com_number){
		const ctx = document.getElementById('instructorChart').getContext('2d');
	
	    const data = {
	        labels: ['Created', 'Committee Member'],
	        datasets: [{
	            label: 'Theses Count',
	            data: [thesis_int_number, thesis_com_number], // Replace with your actual data
	            backgroundColor: [
	                'rgba(54, 162, 235, 0.7)', // Created color
	                'rgba(255, 206, 86, 0.7)'  // Committee member color
	            ],
	            borderColor: [
	                'rgba(54, 162, 235, 1)',
	                'rgba(255, 206, 86, 1)'
	            ],
	            borderWidth: 1
	        }]
	    };

	    const config = {
	        type: 'bar',
	        data: data,
	        options: {
	            plugins: {
	                title: {
	                    display: true,
	                    text: 'Instructor Thesis Activity'
	                }
	            },
	            responsive: true,
	            scales: {
	                y: {
	                    beginAtZero: true
	                }
	            }
	        }
	    };

	    new Chart(ctx, config);
	}
}

function generateGradeChart(instructorGrades, committeeGrades){
	// Static data for now
        

        // Create labels based on the number of theses
        const maxTheses = Math.max(instructorGrades.length, committeeGrades.length);
        const labels = Array.from({ length: maxTheses }, (_, i) => `Thesis ${i + 1}`);

        // Adjust the shorter dataset with "null" to align the bar positions
        const paddedInstructorGrades = Array.from({ length: maxTheses }, (_, i) => instructorGrades[i] || null);
        const paddedCommitteeGrades = Array.from({ length: maxTheses }, (_, i) => committeeGrades[i] || null);

        // Chart configuration
        const ctx = document.getElementById('gradesChart').getContext('2d');
        const gradesChart = new Chart(ctx, {
            type: 'bar',
            data: {
                labels: labels,
                datasets: [
                    {
                        label: 'Instructor Grades',
                        data: paddedInstructorGrades,
                        backgroundColor: 'rgba(75, 192, 192, 0.5)',
                        borderColor: 'rgba(75, 192, 192, 1)',
                        borderWidth: 1,
                    },
                    {
                        label: 'Committee Grades',
                        data: paddedCommitteeGrades,
                        backgroundColor: 'rgba(153, 102, 255, 0.5)',
                        borderColor: 'rgba(153, 102, 255, 1)',
                        borderWidth: 1,
                    },
                ],
            },
            options: {
                responsive: true,
                plugins: {
                    title: {
                        display: true,
                        text: 'Final Grades Comparison',
                    },
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        title: {
                            display: true,
                            text: 'Grades',
                        },
                        suggestedMax: 10, // Grade scale is typically 0-10
                    },
                    x: {
                        title: {
                            display: true,
                            text: 'Theses',
                        },
                    },
                },
            },
        });
}

function generateInstChart(){
	const ctx = document.getElementById('instructorChart').getContext('2d');
	
    const data = {
        labels: ['Created', 'Committee Member'],
        datasets: [{
            label: 'Theses Count',
            data: [4, 1], // Replace with your actual data
            backgroundColor: [
                'rgba(54, 162, 235, 0.7)', // Created color
                'rgba(255, 206, 86, 0.7)'  // Committee member color
            ],
            borderColor: [
                'rgba(54, 162, 235, 1)',
                'rgba(255, 206, 86, 1)'
            ],
            borderWidth: 1
        }]
    };

    const config = {
        type: 'bar',
        data: data,
        options: {
            plugins: {
                title: {
                    display: true,
                    text: 'Instructor Thesis Activity'
                }
            },
            responsive: true,
            scales: {
                y: {
                    beginAtZero: true
                }
            }
        }
    };

    new Chart(ctx, config);
}