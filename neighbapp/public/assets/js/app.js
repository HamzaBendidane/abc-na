requirejs.config({
    "baseUrl": "/assets/js/lib",
    "paths": {
      "app":                    "../app",
      "main":                   "../app/main",
      "bootstrap":              "bootstrap.min",
      "bootswatch":              "bootswatch",
      "app-forms" :             "../app/forms",
      "select2"  :              "select2/select2.min",
      "highcharts" :            "highcharts/highcharts",      
      "daterangepicker":        "daterange/daterangepicker",
      "daterangepickerset":     "daterange/daterangepickerset",
      "moment":                 "daterange/moment",
      'datatables':             "datatables/jquery.datatables",
      'datatables-tabletools':  "datatables/datatables.tabletools"
    }
});

// Dont't use shim. Dependencies are in plugin

// Load main module if not loaded previously
requirejs(["main"]);