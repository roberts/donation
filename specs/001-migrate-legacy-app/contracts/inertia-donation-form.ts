export interface School {
    id: number;
    name: string;
    type: string;
}

export interface DonationForm {
    school_id: number | null;
    amount: number; // User enters dollars, converted to cents on submit
    donor: {
        first_name: string;
        last_name: string;
        email: string;
        phone: string;
    };
    billing_address: {
        street: string;
        city: string;
        state: string;
        zip: string;
        country: string;
    };
    mailing_address: {
        street: string;
        city: string;
        state: string;
        zip: string;
        country: string;
    } | null;
    filing_year: number;
    filing_status: 'single' | 'married_filing_jointly' | 'married_filing_separately';
}

export interface PageProps {
    schools: School[];
    preselected_school_id?: number;
    stripe_key: string;
}
