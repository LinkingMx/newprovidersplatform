interface BrandLogoProps {
    className?: string;
}

export default function BrandLogo({ className = 'h-10' }: BrandLogoProps) {
    return (
        <>
            <img
                src="/images/logo-dark.svg"
                alt="Portal de Proveedores"
                className={`dark:hidden ${className}`}
            />
            <img
                src="/images/logo-light.svg"
                alt="Portal de Proveedores"
                className={`hidden dark:block ${className}`}
            />
        </>
    );
}
