import React, { useState, useEffect } from 'react';
import {
    Table,
    TableBody,
    TableCell,
    TableHead,
    TableHeader,
    TableRow,
} from "@/components/ui/table";
import { Input } from "@/components/ui/input";
import { Button } from "@/components/ui/button";
import { ChevronDown } from "lucide-react";
import {
    Collapsible,
    CollapsibleContent,
    CollapsibleTrigger,
} from "@/components/ui/collapsible";
import { cn } from "@/lib/utils";
import {
    Dialog,
    DialogContent,
    DialogHeader,
    DialogTitle,
    DialogDescription,
    DialogTrigger,
} from "@/components/ui/dialog";

const BentoEvents = () => {
    const [events, setEvents] = useState([]);
    const [newEvent, setNewEvent] = useState({ name: '' });
    const [isOpen, setIsOpen] = useState(false);
    const [isCollapsibleOpen, setIsCollapsibleOpen] = useState(false);

    useEffect(() => {
        fetchEvents();
    }, []);

    const fetchEvents = async () => {
        try {
            const response = await fetch('/cp/bento/events');
            const data = await response.json();
            setEvents(data);
        } catch (error) {
            console.error('Error fetching events:', error);
        }
    };

    const handleSubmit = async (e) => {
        e.preventDefault();
        try {
            let csrfToken = document.cookie
                .split('; ')
                .find(row => row.startsWith('XSRF-TOKEN='))
                ?.split('=')[1];

            if (csrfToken) {
                csrfToken = decodeURIComponent(csrfToken);
            }

            const response = await fetch('/cp/bento/events', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-XSRF-TOKEN': csrfToken,
                    'Accept': 'application/json'
                },
                credentials: 'include',
                body: JSON.stringify(newEvent),
            });

            if (response.ok) {
                setIsOpen(false);
                setNewEvent({ name: '' });

                // Fetch updated events and handle collapsible state
                const updatedEventsResponse = await fetch('/cp/bento/events');
                const updatedEvents = await updatedEventsResponse.json();
                setEvents(updatedEvents);

                // If the total count is now greater than 4, open the collapsible
                if (updatedEvents.length > 4) {
                    setIsCollapsibleOpen(true);
                }
            } else {
                const errorData = await response.json();
                console.error('Server error:', errorData);
            }
        } catch (error) {
            console.error('Error creating event:', error);
        }
    };

    const visibleEvents = events.slice(0, 4);
    const collapsibleEvents = events.slice(4);
    const hasCollapsibleContent = events.length > 4;

    return (
        <div className="card p-4 mt-4">
            <div className="flex justify-between items-center mb-4">
                <h3 className="font-bold text-lg">Bento Events</h3>
                <Dialog open={isOpen} onOpenChange={setIsOpen}>
                    <DialogTrigger asChild>
                        <Button className="btn-primary">Add Event</Button>
                    </DialogTrigger>
                    <DialogContent>
                        <DialogHeader>
                            <DialogTitle>Create New Event</DialogTitle>
                            <DialogDescription>
                                Enter a name for your new event. This will be used to track events in your Bento account.
                            </DialogDescription>
                        </DialogHeader>
                        <form onSubmit={handleSubmit} className="space-y-4">
                            <div>
                                <label className="text-sm font-medium">Event Name</label>
                                <Input
                                    value={newEvent.name}
                                    onChange={(e) => setNewEvent({ name: e.target.value })}
                                    placeholder="Enter event name"
                                    className="mt-1"
                                    required
                                />
                            </div>
                            <Button type="submit" className="w-full">Create Event</Button>
                        </form>
                    </DialogContent>
                </Dialog>
            </div>

            <Table>
                <TableHeader>
                    <TableRow>
                        <TableHead>Name</TableHead>
                    </TableRow>
                </TableHeader>
                <TableBody>
                    {visibleEvents.map((event) => (
                        <TableRow key={event.id}>
                            <TableCell>{event.name}</TableCell>
                        </TableRow>
                    ))}
                </TableBody>
            </Table>

            {hasCollapsibleContent && (
                <Collapsible
                    open={isCollapsibleOpen}
                    onOpenChange={setIsCollapsibleOpen}
                >
                    <CollapsibleContent>
                        <Table>
                            <TableBody>
                                {collapsibleEvents.map((event) => (
                                    <TableRow key={event.id}>
                                        <TableCell>{event.name}</TableCell>
                                    </TableRow>
                                ))}
                            </TableBody>
                        </Table>
                    </CollapsibleContent>

                    <CollapsibleTrigger className="flex justify-center w-full mt-2">
                        <div className="hover:bg-zinc-100 dark:hover:bg-zinc-800/50 p-2 flex items-center gap-2 text-zinc-400">
                            <ChevronDown
                                className={cn(
                                    "h-4 w-4 transition-transform duration-200",
                                    isCollapsibleOpen && "rotate-180"
                                )}
                            />
                            <span className="text-xs">
                                {isCollapsibleOpen ? "Show Less" : "Show More"}
                            </span>
                        </div>
                    </CollapsibleTrigger>
                </Collapsible>
            )}
        </div>
    );
};

export default BentoEvents;
