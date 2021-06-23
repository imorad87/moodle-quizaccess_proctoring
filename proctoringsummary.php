<?php
require_once (__DIR__ . '/../../../../config.php');
require_once ($CFG->dirroot . '/lib/tablelib.php');
require_once (__DIR__ . '/classes/addtional_settings_helper.php');

$cmid = required_param('cmid', PARAM_INT);
$context = context_module::instance($cmid, MUST_EXIST);
require_capability('quizaccess/proctoring:deletecamshots', $context);

$params = array(
    'cmid' => $cmid
);
$url = new moodle_url(
    '/mod/quiz/accessrule/proctoring/proctoringsummary.php',
    $params
);

list ($course, $cm) = get_course_and_cm_from_cmid($cmid, 'quiz');

require_login($course, true, $cm);

$PAGE->set_url($url);
$PAGE->set_title('Proctoring Summary Report');
$PAGE->set_heading('Proctoring Summary Report');

$PAGE->navbar->add('Proctoring Report', $url);
$PAGE->requires->js_call_amd('quizaccess_proctoring/additionalSettings', 'setup',array());

echo $OUTPUT->header();

$coursewisesummarysql = 'SELECT
                        MC.fullname as coursefullname,
                        MC.shortname as courseshortname,
                        MQL.courseid,
                        COUNT(MQL.id) as logcount
                        FROM {quizaccess_proctoring_logs} MQL
                        JOIN {course} MC ON MQL.courseid = MC.id  
                        GROUP BY courseid,coursefullname,courseshortname';
$coursesummary = $DB->get_records_sql($coursewisesummarysql);


$quizsummarysql = 'SELECT
                    CM.id as quizid,
                    MQ.name,
                    MQL.courseid,
                    COUNT(MQL.id) as logcount
                    FROM mdl_quizaccess_proctoring_logs MQL
                    JOIN mdl_course_modules CM ON MQL.quizid = CM.id
                    JOIN mdl_quiz MQ ON CM.instance = MQ.id
                    GROUP BY MQ.id';
$quizsummary = $DB->get_records_sql($quizsummarysql);

//$settingsparams = array();
//$settingsparams['section'] = 'modsettingsquizcatproctoring';
//$mainsettingsurl = new moodle_url(
//    '/admin/settings.php',
//    $settingsparams
//);
//$title = "Summary Report";
//$mainsettingspagebtn = get_string('mainsettingspagebtn', 'quizaccess_proctoring');
//echo '<div>
//        <h1>'.$title.'</h1>
//      </div>
//      <a class="btn btn-primary" href="'.$mainsettingsurl.'">'.$mainsettingspagebtn.'</a>
//      <br/>
//      <br/>
//      ';

echo '<div class="box generalbox m-b-1 adminerror alert alert-info p-y-1">'
    . get_string('summarypagedesc', 'quizaccess_proctoring') . '</div>';

echo '<table class="flexible table table_class">
        <thead>
            <th colspan="2">Course Name / Quiz Name</th>
            <th>Number of images</th>
            <th>Delete</th>
        </thead>';

echo '<tbody>';

foreach ($coursesummary as $row){
    $params1 = array(
        'cmid' => $cmid,
        'type' => 'course',
        'id' => $row->courseid
    );
    $url1 = new moodle_url(
        '/mod/quiz/accessrule/proctoring/bulkdelete.php',
        $params1
    );
    $deletelink1 = '<a onclick="return confirm(`Are you sure want to delete the pictures for this course?`)" href="'.$url1.'"><i class="icon fa fa-trash fa-fw "></i></a>';

    echo '<tr class="course-row no-border">';
    echo '<td colspan="2" class="no-border">'.$row->courseshortname.":".$row->coursefullname."</td>";
//    echo '<td>'.$row->coursefullname."</td>";
//    echo '<td>'."</td>";
    echo '<td class="no-border">'.$row->logcount."</td>";
    echo '<td class="no-border">'.$deletelink1."</td>";
    echo '</tr>';

    foreach ($quizsummary as $row2){
        if($row->courseid == $row2->courseid){
            $params2 = array(
                'cmid' => $cmid,
                'type' => 'quiz',
                'id' => $row2->quizid
            );
            $url2 = new moodle_url(
                '/mod/quiz/accessrule/proctoring/bulkdelete.php',
                $params2
            );
            $deletelink2 = '<a onclick="return confirm(`Are you sure want to delete the pictures for this quiz?`)" href="'.$url2.'"><i class="icon fa fa-trash fa-fw "></i></a>';

            echo '<tr class="quiz-row">';
            echo '<td width="5%" class="no-border"></td>';
            echo '<td class="no-border">'.$row2->name."</td>";
            echo '<td class="no-border">'.$row2->logcount."</td>";
            echo '<td class="no-border">'.$deletelink2."</td>";
            echo '</tr>';
        }
    }
}
echo '</tbody></table>';

echo '<style>
.table_class{
  font-family: arial, sans-serif;
  border-collapse: collapse;
  width: 100%;
}

.course-row{
  background-color: #dddddd;
  border: none;
}

.quiz-row{
  background-color: #ffffff;
  border: none;
}

.no-border{
    border: none !important;
    border-top: none !important;
}
</style>';