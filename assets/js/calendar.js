const calendar = new tui.Calendar('#calendar', {
   defaultView: 'month',
   useCreationPopup: true,
   useDetailPopup: true,
   taskView: false,
   scheduleView: ['time'],
   useNav: true,
   month: {
       daynames: ['Sun', 'Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat'],
       startDayOfWeek: 1,
       narrowWeekend: false,
       visibleWeeksCount: 6,
       isAlways6Week: true
   }
});
