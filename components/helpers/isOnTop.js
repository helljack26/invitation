import { useState, useEffect, useCallback } from 'react';


export const isOnTop = (scrollY = 0) => {
    const [result, setResult] = useState({ onTop: true });

    const handleNavigation = useCallback(y => {
        if (y < 10) {
            return setResult({ onTop: true })
        } else {
            return setResult({ onTop: false })
        }
    }, [scrollY]
    );

    useEffect(() => {
        if (scrollY) {
            handleNavigation(scrollY)
        }
    }, [scrollY]);

    return result

}

