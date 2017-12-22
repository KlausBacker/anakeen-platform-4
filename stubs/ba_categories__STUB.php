<?php
namespace Dcp\Family {
	/**   */
	class Ba_categories extends Document { const familyName="BA_CATEGORIES";}
}

namespace Dcp\AttributeIdentifiers {
	/**   */
	class Ba_categories {
		/** [frame] Catégories */
		const cat_fr_ident='cat_fr_ident';
		/** [array] Catégories */
		const cat_t_categories='cat_t_categories';
		/** [text] Nom */
		const cat_name='cat_name';
		/** [money] Montant Maximum */
		const cat_max='cat_max';
		/** [enum] Période */
		const cat_period='cat_period';
	}
}
