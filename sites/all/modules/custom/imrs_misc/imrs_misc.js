var abbreviations = {
  "AEB": "Brazilian Space Agency",
  "ATV": "All-Terrain Vehicle",
  "AWESOM": "Autonomous Water Extraction from the Surface Of Mars",
  "CEO": "Chief Executive Officer",
  "CH4": "Methane",
  "CME": "Coronal Mass Ejection",
  "CNSA": "China National Space Administration",
  "COTS": "Commercial Off The Shelf",
  "CSA": "Canadian Space Agency",
  "DRA": "Design Reference Architecture",
  "ECLSS": "Environment Control and Life Support System",
  "EDL": "Entry, Descent and Landing",
  "EELV": "Evolved Expendable Launch Vehicle",
  "EHA": "Extra-Habitat Activity",
  "ESA": "European Space Agency",
  "EVA": "Extra-Vehicular Activity",
  "FMARS": "Flashline Mars Arctic Research Station",
  "GNC": "Guidance, Navigation and Control",
  "GPS": "Global Positioning System",
  "H2M": "Humans to Mars",
  "HLLV": "Heavy Lift Launch Vehicle",
  "IMRS": "International Mars Research Station",
  "ISA": "Iranian Space Agency",
  "ISAP": "In Situ Air Production",
  "ISPP": "In Situ Propellant Production",
  "ISRO": "Indian Space Research Organisation",
  "ISRU": "In Situ Resource Utilisation",
  "ISS": "International Space Station",
  "JAXA": "Japan Aerospace Exploration Agency",
  "KARI": "Korea Aerospace Research Institute",
  "L5CS": "L5 Communications Satellite",
  "LH2": "Liquid Hydrogen",
  "LEO": "Low Earth Orbit",
  "LFTR": "Liquid Fluoride Thorium Reactor",
  "LION": "Landing with Inertial and Optical Navigation",
  "LOX": "Liquid Oxygen",
  "LPS": "Local Positioning System",
  "MARS": "Mars Analog Research Station",
  "MAV": "Mars Ascent Vehicle",
  "MCOS": "Mars Communications and Observation Satellite",
  "MDRS": "Mars Desert Research Station",
  "MLLV": "Medium Lift Launch Vehicle",
  "MOLA": "Mars Orbiting Laser Altimeter",
  "MSH": "Mars Surface Habitat",
  "MSV": "Mars Surface Vehicle",
  "MTH": "Mars Transit Habitat",
  "MTV": "Mars Transfer Vehicle",
  "NASA": "National Aeronautics and Space Administration",
  "NTR": "Nuclear Thermal Rocket",
  "PHP-M": "Permanent Human Presence on Mars",
  "RCS": "Reaction Control System",
  "ROI": "Return On Investment",
  "RP1": "Rocket Propellant 1",
  "RWGS": "Reverse Water Gas Shift",
  "SLS": "Space Launch System",
  "TRL": "Technology Readiness Level",
  "USA": "United States of America",
  "VTOL": "Vertical Take-Off and Landing"
};

var compounds = {
  "Ar": "argon",
  "CH4": "methane",
  "H2": "hydrogen",
  "H2O": "water",
  "O2": "oxygen",
  "N2": "nitrogen"
};

(function($) {

  function autoAbbr() {
    var bookPage = $('.node--book--full');
    var abbr, html;
    if (bookPage.length) {
      // Replace any abbreviations:
      html = bookPage.html();
      for (abbr in abbreviations) {
        html = html.replace(new RegExp("\\b" + abbr + "\\b", 'g'), "<abbr title='" + abbreviations[abbr] + "'>" + abbr + "</abbr>");
      }
      bookPage.html(html);
    }
  }

  function init() {
    $('.node--book--full').tooltip({
      items: "abbr",
      position: {
        my: "left-5 bottom-3",
        at: "left top"
      },
      show: false
    });
    autoAbbr();
  }

  $(init);

})(jQuery);
