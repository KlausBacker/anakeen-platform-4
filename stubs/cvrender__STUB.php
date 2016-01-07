<?php
namespace Dcp\Family {
	/** Contrôle de rendu  */
	class Cvrender extends \Dcp\Ui\Cvrender { const familyName="CVRENDER";}
}

namespace Dcp\AttributeIdentifiers {
	/** Contrôle de rendu  */
	class Cvrender extends Cvdoc {
		/** [text] Classe de configuration de rendu */
		const cv_renderconfigclass='cv_renderconfigclass';
		/** [text] Classe d'accès au rendu */
		const cv_renderaccessclass='cv_renderaccessclass';
	}
}
