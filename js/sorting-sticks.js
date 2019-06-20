function sorting_sticks(num) {
    
    var num_students = num;

    per = Math.floor(num_students/2);
    extra_temp = num_students - (per * 2);

    extra = extra_temp+' groups will have an additional student.';

    jQuery('#numbers_per').html(per);
    jQuery('#numbers_extra').html(extra);

    per = Math.floor(num_students/3);
    extra_temp = num_students - (per * 3);

    extra = extra_temp+' groups will have an additional student.';

    jQuery('#color_shape_per').html(per);
    jQuery('#color_shape_extra').html(extra);

    per = Math.floor(num_students/4);
    extra_temp = num_students - (per * 4);

    extra = extra_temp+' groups will have an additional student.';

    jQuery('#color_bar_per').html(per);
    jQuery('#color_bar_extra').html(extra);

    per = Math.floor(num_students/5);
    extra_temp = num_students - (per * 5);

    extra = extra_temp+' groups will have an additional student.';


    jQuery('#trans_per').html(per);
    jQuery('#trans_extra').html(extra);

    per = Math.floor(num_students/6);
    extra_temp = num_students - (per * 6);

    extra = extra_temp+' groups will have an additional student.';

    jQuery('#shape_per').html(per);
    jQuery('#shape_extra').html(extra);

    per = Math.floor(num_students/7);
    extra_temp = num_students - (per * 7);


    extra = extra_temp+' groups will have an additional student.';


    jQuery('#sport_per').html(per);
    jQuery('#sport_extra').html(extra);

    groups = Math.ceil(num_students/2);
    extra_temp = groups*2 - num_students;
 
    extra = extra_temp+' groups will have a single student.';

    jQuery('#alpha_groups').html(groups);
    jQuery('#alpha_extra').html(extra);

}