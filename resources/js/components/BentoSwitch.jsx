import React, { useState, useEffect } from 'react';
import { Switch } from '@/components/ui/switch';
import { Label } from '@/components/ui/label';

const BentoSwitch = ({ id, label, checked: initialChecked = false }) => {
    const [isChecked, setIsChecked] = useState(initialChecked);

    useEffect(() => {
        console.log('BentoSwitch mounted with initial checked:', initialChecked);
    }, []);

    const handleChange = (checked) => {
        console.log('Switch toggled:', checked);
        setIsChecked(checked);

        // Update the hidden input value
        const hiddenInput = document.querySelector(`input[name="${id}"]`);
        if (hiddenInput) {
            hiddenInput.value = checked ? "1" : "0";
            console.log('Updated hidden input value to:', hiddenInput.value);
        } else {
            console.warn(`Hidden input with name "${id}" not found`);
        }
    };

    return (
        <div className="flex items-center space-x-2">
            <Switch
                id={id}
                checked={isChecked}
                onCheckedChange={handleChange}
                className="data-[state=checked]:bg-blue-500 dark:data-[state=checked]:bg-blue-300"
            />

        </div>
    );
};

export default BentoSwitch;
