function singleDateCheck(month) {
    if (month < 10) {
        return '0' + month;
    }

    return month;
}

function numDateCheck(month) {
    if (month.substr(0,1) == 0) {
        return month.substr(1, 1);
    }

    return month;
}

function dateToZhcn(date) {
    alert(JSON.stringify(data));
    return;
    date = date.replace("-", "年");
    date = date.replace("-", "月");

    return date + "日";
}