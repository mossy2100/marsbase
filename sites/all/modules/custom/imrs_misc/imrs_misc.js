var abbreviations = {
  "AEB": "Brazilian Space Agency",
  "ATV": "All-Terrain Vehicle",
  "AWESOM": "Autonomous Water Extraction from the Surface Of Mars",
  "CEO": "Chief Executive Officer",
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
  "ISFP": "In Situ Food Production",
  "ISPP": "In Situ Propellant Production",
  "ISRO": "Indian Space Research Organisation",
  "ISRU": "In Situ Resource Utilisation",
  "ISS": "International Space Station",
  "ISWP": "In Situ Water Production",
  "IVA": "Intra-Vehicular Activity",
  "JAXA": "Japan Aerospace Exploration Agency",
  "KARI": "Korea Aerospace Research Institute",
  "L5CS": "L5 Communications Satellite",
  "LCH4": "Liquid Methane",
  "LED": "Light-Emitting Diode",
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
  "RTG": "Radioisotope Thermoelectric Generator",
  "RWGS": "Reverse Water Gas Shift",
  "SLS": "Space Launch System",
  "TRL": "Technology Readiness Level",
  "USA": "United States of America",
  "VASIMR": "Variable Specific Impulse Magnetoplasma Rocket",
  "VTOL": "Vertical Take-Off and Landing",
  "WAVAR": "Water Vapour Adsorption Reactor"
};

var formulae = {
  "Ar": "argon",
  "CH4": "methane",
  "CO2": "carbon dioxide",
  "H2": "hydrogen",
  "H2O": "water",
  "O2": "oxygen",
  "N2": "nitrogen"
};

(function($) {

  function markupAbbreviations(el) {
    if (el.nodeType == 3) {
      var abbr, abbrWithSubscripts;
      el = $(el);

      var text = el.text();
      var html;

      if (text) {
        html = text;

        // Replace any abbreviations:
        for (abbr in abbreviations) {
          html = html.replace(new RegExp("\\b" + abbr + "\\b", 'g'), "<abbr title='" + abbreviations[abbr] + "'>" + abbr + "</abbr>");
        }

        // Replace any chemical formulae:
        for (abbr in formulae) {
          // Wrap every sequence of digits in <sub> tags:
          abbrWithSubscripts = abbr.replace(/(\d+)/g, "<sub>$1</sub>");
          html = html.replace(new RegExp("\\b" + abbr + "\\b", 'g'), "<abbr title='" + formulae[abbr] + "'>" + abbrWithSubscripts + "</abbr>");
        }

        if (html != text) {
          el.replaceWith(html);
        }
      }
    }

    // Recurse into children:
    $(el).contents().each(function(i, el) {
      markupAbbreviations(el);
    });
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

    var bookPages = $('article.node--book--full');
    if (bookPages.length) {
      markupAbbreviations(bookPages.get(0));
    }
  }

  $(init);

})(jQuery);
